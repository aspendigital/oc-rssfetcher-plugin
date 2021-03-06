<?php

declare(strict_types=1);

namespace Vdlp\RssFetcher\Components;

use Vdlp\RssFetcher\Models\Item;
use Cms\Classes\ComponentBase;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

/**
 * Class PaginatableItems
 *
 * @package Vdlp\RssFetcher\Components
 */
class PaginatableItems extends ComponentBase
{
    /**
     * @var LengthAwarePaginator
     */
    public $items;

    /**
     * {@inheritdoc}
     */
    public function componentDetails(): array
    {
        return [
            'name' => 'vdlp.rssfetcher::lang.component.paginatable_item_list.name',
            'description' => 'vdlp.rssfetcher::lang.component.paginatable_item_list.description',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function defineProperties(): array
    {
        return [
            'itemsPerPage' => [
                'title' => 'vdlp.rssfetcher::lang.item.items_per_page',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'vdlp.rssfetcher::lang.item.items_per_page_validation',
                'default' => '10',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function onRun()
    {
        $this->items = $this->loadItems();
    }

    /**
     * Load Items
     *
     * @return LengthAwarePaginator|array
     */
    protected function loadItems()
    {
        try {
            $items = Item::query()
                ->select(['vdlp_rssfetcher_items.*', 'vdlp_rssfetcher_sources.name AS source'])
                ->join(
                    'vdlp_rssfetcher_sources',
                    'vdlp_rssfetcher_items.source_id',
                    '=',
                    'vdlp_rssfetcher_sources.id'
                )
                ->where('vdlp_rssfetcher_sources.is_enabled', '=', 1)
                ->where('vdlp_rssfetcher_items.is_published', '=', 1)
                ->orderBy('vdlp_rssfetcher_items.pub_date', 'desc')
                ->paginate($this->property('itemsPerPage'));
        } catch (InvalidArgumentException $e) {
            return [];
        }

        return $items;
    }
}
