<?php

namespace App\Repositories;

use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\AuthHelperController;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseRepository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Application
     */
    protected $app;

    /** @var AuthHelperController $authHelper*/
    private $authHelper;

    /**
     * @param Application $app
     *
     * @throws \Exception
     */
    public function __construct(Application $app, AuthHelperController $authHelper)
    {
        $this->app = $app;
        $this->makeModel();
        $this->authHelper = $authHelper;
    }

    /**
     * Get searchable fields array
     *
     * @return array
     */
    abstract public function getFieldsSearchable();

    /**
     * Configure the Model
     *
     * @return string
     */
    abstract public function model();

    /**
     * Make Model instance
     *
     * @throws \Exception
     *
     * @return Model
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Paginate records for scaffold.
     *
     * @param int $perPage
     * @param array $columns
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage, $columns = ['*'])
    {
        $query = $this->allQuery();

        return $query->paginate($perPage, $columns);
    }

    /**
     * Build a query for retrieving all records.
     *
     * @param array $search
     * @param int|null $skip
     * @param int|null $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function allQuery($search = [], $skip = null, $limit = null)
    {
        $query = $this->model->newQuery();

        if (count($search)) {
            foreach ($search as $key => $value) {
                if (in_array($key, $this->getFieldsSearchable())) {
                    $query->where($key, $value);
                }
            }
        }

        if (!is_null($skip)) {
            $query->skip($skip);
        }

        if (!is_null($limit)) {
            $query->limit($limit);
        }

        return $query;
    }

    /**
     * Retrieve all records with given filter criteria
     *
     * @param array $search
     * @param int|null $skip
     * @param int|null $limit
     * @param array $columns
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all($search = [], $skip = null, $limit = null, $columns = ['*'])
    {
        $query = $this->allQuery($search, $skip, $limit);

        return $query->get($columns);
    }


    public function allWithOrder($search = [], $orderBy = 'created_at', $orderDirection = 'asc', $columns = ['*'])
    {
        $query = $this->allQuery($search);

        return $query->orderBy($orderBy, $orderDirection)->get($columns);
    }
    /**
     * Create model record
     *
     * @param array $input
     *
     * @return Model
     */
    public function create($input)
    {
        $userId = $this->authHelper->getAuthenticatedUser();
        // Attach the user ID to the input data
        $input['created_by'] = $userId;
        $input['updated_by'] = $userId;
        $input['deleted_by'] = 0;

        $model = $this->model->newInstance($input);

        $model->save();

        return $model;
    }

    /**
     * Create model record
     *
     * @param array $input
     *
     * @return Model
     */
    public function createwithoutuser($input)
    {
        $model = $this->model->newInstance($input);

        $model->save();

        return $model;
    }

    /**
     * Find model record for given id
     *
     * @param int $id
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function find($id, $columns = ['*'])
    {
        $query = $this->model->newQuery();

        return $query->find($id, $columns);
    }

    /**
     * Update model record for given id
     *
     * @param array $input
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model
     */
    public function update($input, $id)
    {
        $userId = $this->authHelper->getAuthenticatedUser();
        // Attach the user ID to the input data
        $input['created_by'] = $userId;
        $input['updated_by'] = $userId;
        $input['deleted_by'] = 0;

        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);

        $model->fill($input);

        $model->save();

        return $model;
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     *
     * @return bool|mixed|null
     */
    public function delete($id)
    {
        $userId = $this->authHelper->getAuthenticatedUser();
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);
        // Set the value of the 'deleted_by' column
        $model->deleted_by = $userId;
        // Save the changes to the model
        $model->save();

        return $model->delete();
    }

    /**
     * Retrieve all records with the name of the user who created them
     *
     * @param array $search
     * @param int|null $skip
     * @param int|null $limit
     * @param array $columns
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allWithUser($search = [], $skip = null, $limit = null, $columns = ['*'])
    {
        // Build query with eager loading of 'creator' relationship
        $query = $this->allQuery($search, $skip, $limit)->with('creator');
        return $query->get($columns);
    }
}
