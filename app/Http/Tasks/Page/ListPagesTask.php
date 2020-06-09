<?php

namespace App\Http\Tasks\Page;

use App\Http\Tasks\Task;
use App\Repositories\PageRepository;
use Illuminate\Contracts\Pagination\Paginator;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class ListPagesTask
 *
 * @package App\Http\Tasks\Page
 */
class ListPagesTask extends Task
{
    /**
     * @var PageRepository $pageRepository
     */
    protected $pageRepository;

    /**
     * @param PageRepository $pageRepository
     */
    public function __construct(
        PageRepository $pageRepository
    ) {
        $this->pageRepository = $pageRepository;
    }

    /**
     * @param array $data
     *
     * @return Paginator
     * @throws RepositoryException
     * @throws \InvalidArgumentException
     */
    public function run(array $data): ?Paginator {
        return $this
            ->pageRepository
            ->applyCustomCriteria($data)
            ->paginate($this->getItemsPerPage($data))
        ;
    }
}
