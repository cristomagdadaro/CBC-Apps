<?php

namespace App\Repositories;

use App\Enums\Inventory;
use App\Models\LaboratoryEquipmentLocationSurvey;
use App\Models\Transaction;
use App\Models\TransactionComponent;
use App\Models\Category;
use App\Repositories\OptionRepo;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use App\Pipelines\InventoryTransaction\ResolveUserByEmployeeId;
use App\Pipelines\InventoryTransaction\AssignTransactionUuid;
use App\Pipelines\InventoryTransaction\PersistTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TransactionRepo extends AbstractRepoService
{
    public function __construct(Transaction $model, private readonly OptionRepo $optionRepo)
    {
        parent::__construct($model);
        $this->appendWith = ['item', 'user','personnel'];
    }

    protected function buildSearchQuery(Collection $parameters, bool $withPagination, bool $isTrashed)
    {
        $builder = $this->model->newQuery();
        
        $this->applyAppends($builder, $parameters);
        $this->applyParentFilter($builder, $parameters);
        $this->applySearchFilters($builder, $parameters);
        $this->applyGroupBy($builder, $parameters);
        $this->applySorting($builder, $parameters);

        if ($transacType = $parameters->get('transac_type')) {
            $builder->where('transactions.transac_type', $transacType);
        }

        if ($withPagination) {
            return $this->applyPagination($builder, $parameters);
        }

        return $builder->get();
    }

    public function create(array $data)
    {
        $parentBarcode = $this->normalizeParentBarcode($data['parent_barcode'] ?? null);

        unset($data['components'], $data['parent_barcode']);

        return DB::transaction(function () use ($data, $parentBarcode) {
            /** @var Transaction $main */
            $main = $this->model->newQuery()->create($data);

            $this->syncParentTransactionLink($main, $parentBarcode);

            return $main;
        });
    }

    public function delete(int|string $id): Model
    {
        return DB::transaction(function () use ($id) {
            $model = $this->model->newQuery()->findOrFail($id);
            $deletedData = $model->getAttributes();

            $model->components()->delete();
            $model->parentComponents()->delete();
            $model->reports()->delete();
            $model->delete();

            $model->setRawAttributes($deletedData);

            return $model;
        });
    }

    public function forceDelete(int|string $id): Model
    {
        return DB::transaction(function () use ($id) {
            $model = $this->model->newQuery()->withTrashed()->findOrFail($id);
            $deletedData = $model->getAttributes();

            $model->components()->withTrashed()->forceDelete();
            $model->parentComponents()->withTrashed()->forceDelete();
            $model->reports()->withTrashed()->forceDelete();
            $model->forceDelete();

            $model->setRawAttributes($deletedData);

            return $model;
        });
    }

    public function update(int|string $id, array $data): Model
    {
        $parentBarcode = $this->normalizeParentBarcode($data['parent_barcode'] ?? null);

        unset($data['components'], $data['parent_barcode']);

        return DB::transaction(function () use ($id, $data, $parentBarcode) {
            /** @var Transaction $model */
            $model = $this->model->newQuery()->findOrFail($id);
            $model->fill($data);
            $model->save();

            if (($model->transac_type ?? null) !== 'incoming') {
                $model->components()->delete();
                $model->parentComponents()->delete();
                return $model;
            }

            $this->syncParentTransactionLink($model, $parentBarcode);

            return $model;
        });
    }

    private function normalizeParentBarcode(?string $barcode): ?string
    {
        if ($barcode === null) {
            return null;
        }

        $normalized = trim($barcode);

        return $normalized === '' ? null : $normalized;
    }

    private function resolveParentTransaction(string $barcode, ?string $excludeId = null): ?Transaction
    {
        return $this->model->newQuery()
            ->where('transac_type', Inventory::INCOMING->value)
            ->when($excludeId, fn (Builder $query) => $query->where('id', '!=', $excludeId))
            ->where(function (Builder $query) use ($barcode) {
                $query->where('barcode', $barcode)
                    ->orWhere('barcode_prri', $barcode);
            })
            ->first();
    }

    private function syncParentTransactionLink(Transaction $transaction, ?string $parentBarcode): void
    {
        if (($transaction->transac_type ?? null) !== Inventory::INCOMING->value) {
            $transaction->parentComponents()->delete();

            return;
        }

        if ($parentBarcode === null) {
            $transaction->parentComponents()->delete();

            return;
        }

        $parent = $this->resolveParentTransaction($parentBarcode, $transaction->id);

        if (! $parent) {
            throw ValidationException::withMessages([
                'parent_barcode' => ['The parent barcode does not match an existing incoming transaction.'],
            ]);
        }

        $transaction->parentComponents()
            ->where('transaction_id', '!=', $parent->id)
            ->delete();

        $existingLink = TransactionComponent::query()
            ->withTrashed()
            ->where('transaction_id', $parent->id)
            ->where('component_transaction_id', $transaction->id)
            ->first();

        if ($existingLink) {
            if ($existingLink->trashed()) {
                $existingLink->restore();
            }

            return;
        }

        TransactionComponent::query()->create([
            'transaction_id' => $parent->id,
            'component_transaction_id' => $transaction->id,
        ]);
    }

    

    public function applySorting(Builder &$query, Collection $parameters): void
    {
        $sortColumn = $parameters->get('sort');

        if (!$sortColumn) {
            $query->orderBy('transactions.created_at', 'desc');
            return;
        }

        parent::applySorting($query, $parameters);
    }

    public function createOutgoingWithPipeline(array $validated): Model
    {
        $context = app(Pipeline::class)
            ->send([
                'repo' => $this,
                'payload' => $validated,
                'model' => null,
            ])
            ->through([
                ResolveUserByEmployeeId::class,
                AssignTransactionUuid::class,
                PersistTransaction::class,
            ])
            ->thenReturn();

        return $context['model'];
    }

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    

    
}
