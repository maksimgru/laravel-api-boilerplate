<?php

namespace App\Http\Controllers;

use App\Constants\RouteConstants;
use App\Models\User;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use InvalidArgumentException;
use Specialtactics\L5Api\Http\Controllers\RestfulController as BaseController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Controller
 *
 * @SWG\Swagger(
 *     basePath="L5_SWAGGER_BASE_PATH",
 *     schemes={"https", "http"},
 *     host=L5_SWAGGER_CONST_HOST,
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="Loyalty API doc",
 *         description="Loyalty Swagger API description.",
 *     )
 * )
 *
 * @package App\Http\Controllers
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @return int
     */
    public function getItemsPerPage(): int {
        $per_page = (int) request()->input(RouteConstants::REQUEST_FIELD_NAME_PER_PAGE);
        //$per_page = $per_page ?: (int) (new static::$model)->getPerPage();
        $per_page = $per_page ?: config('repository.pagination.limit');

        return $per_page;
    }

    /**
     * Request to retrieve a single soft deleted item of this resource
     *
     * @param int $modelId
     *
     * @return Response
     * @throws HttpException
     */
    public function getSoftDeleted(int $modelId)
    {
        $model = new static::$model;

        $resource = $model::onlyTrashed()->with($model::getItemWith())->where($model->getKeyName(), '=', $modelId)->first();

        if (!$resource) {
            throw new NotFoundHttpException('Resource \'' . class_basename(static::$model) . '\' with given UUID ' . $modelId . ' not found');
        }

        $this->authorizeUserAction('view', $resource);

        return $this->response->item($resource, $this->getTransformer());
    }

    /**
     * Force Delete Model
     *
     * @param int $modelId
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     * @throws InvalidArgumentException
     * @throws HttpException
     */
    public function forceDelete(int $modelId): Response {
        /** @var Model $resource */
        $resource = static::$model::withTrashed()->findOrFail($modelId);

        $this->authorizeUserAction('delete', $resource);

        $deletedCount = $resource->forceDelete();

        if ($deletedCount < 1) {
            throw new NotFoundHttpException(trans('Could not find a resource with that ID to delete'));
        }

        return $this->response->noContent()->setStatusCode(Response::HTTP_NO_CONTENT);
    }

    /**
     * Restore Model
     *
     * @param int $modelId
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     * @throws InvalidArgumentException
     * @throws HttpException
     */
    public function restore(int $modelId): Response {
        /** @var Model $resource */
        $resource = static::$model::onlyTrashed()->findOrFail($modelId);

        $this->authorizeUserAction('restore', $resource);

        $resource->restore();

        return $this->response->item($resource, $this->getTransformer());
    }
}
