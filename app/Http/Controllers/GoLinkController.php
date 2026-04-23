<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGoLinkRequest;
use App\Http\Requests\DeleteGoLinkRequest;
use App\Http\Requests\GetGoLinkRequest;
use App\Http\Requests\UpdateGoLinkRequest;
use App\Models\GoLinkRedirect;
use App\Repositories\GoLinkRepo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class GoLinkController extends BaseController
{
    public function __construct(private readonly GoLinkRepo $repository)
    {
        $this->service = $repository;
    }

    public function index(GetGoLinkRequest $request): Collection
    {
        return parent::_index($request);
    }

    public function create(CreateGoLinkRequest $request): Model
    {
        return parent::_store($request);
    }

    public function update(UpdateGoLinkRequest $request, string $id): Model
    {
        return parent::_update($id, $request);
    }

    public function destroy(DeleteGoLinkRequest $request, string $id): Model
    {
        return parent::_destroy($request->validated('id') ?: $id);
    }

    public function redirect(string $slug)
    {
        $goLink = $this->repository->findForRedirect($slug);

        if (! $goLink instanceof GoLinkRedirect || ! $goLink->status) {
            return redirect()->to((string) config('app.url'));
        }

        if ($goLink->is_expired) {
            return response()
                ->view('golinks.expired', ['goLink' => $goLink], Response::HTTP_GONE)
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        $this->repository->incrementClicks($goLink);

        return response()
            ->view('golinks.redirect', ['goLink' => $goLink], Response::HTTP_OK)
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
