<?php

namespace App\Repositories;

use App\Models\SuppEquipReport;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SuppEquipReportRepo extends AbstractRepoService
{
    private OptionRepo $optionRepo;

    public function __construct(SuppEquipReport $model, OptionRepo $optionRepo)
    {
        parent::__construct($model);

        $this->optionRepo = $optionRepo;
        $this->appendWith = ['transaction.item', 'transaction.user', 'transaction.personnel', 'item', 'user'];
    }

    public function createWithTransaction(array $validated, ?string $userId): Model
    {
        $transaction = Transaction::with('item')->findOrFail($validated['transaction_id']);

        $payload = array_merge($validated, [
            'item_id' => $transaction->item_id,
            'reported_at' => $validated['reported_at'] ?? now(),
            'user_id' => $userId,
        ]);

        return $this->loadFormRelations($this->create($payload));
    }

    public function updateWithTransaction(string $id, array $validated): Model
    {
        $transaction = Transaction::with('item')->findOrFail($validated['transaction_id']);
        $validated['item_id'] = $transaction->item_id;

        return $this->loadFormRelations($this->update($id, $validated));
    }

    public function getFormData(string $id): SuppEquipReport
    {
        $report = $this->model->newQuery()
            ->with($this->appendWith)
            ->findOrFail($id);

        return $this->loadFormRelations($report);
    }
    public function getReportTemplates(): array
    {
        return $this->optionRepo->getWithMetadata('supp_equip_report_templates')
            ->mapWithKeys(function ($option) {
                $options = is_array($option->options) ? $option->options : json_decode($option->options, true);
                
                return [
                    $option->key => [
                        'label' => $option->label,
                        'description' => $option->description,
                        'fields' => $options['fields'] ?? [],
                    ]
                ];
            })
            ->toArray();
    }




    protected function loadFormRelations(Model $report): Model
    {
        return $report->loadMissing($this->appendWith);
    }
}
