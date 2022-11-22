<?php

namespace RoleExpire\Mcp;

use ExpressionEngine\Service\Addon\Controllers\Mcp\AbstractRoute;
use ExpressionEngine\Library\CP\Table;

class Index extends AbstractRoute
{
    /**
     * @var string
     */
    protected $route_path = 'index';

    /**
     * @var string
     */
    protected $cp_page_title = 'home';

    /**
     * @param false $id
     * @return AbstractRoute
     */
    public function process($id = false)
    {
        $sort_col = ee('Request')->get('sort_col') ?: 're.role.id';
        $sort_dir = ee('Request')->get('sort_dir') ?: 'desc';
        $this->per_page = ee('Request')->get('perpage') ?: $this->per_page;

        $query = [
            'sort_col' => $sort_col,
            'sort_dir' => $sort_dir,
        ];

        $base_url = ee('CP/URL')->make($this->base_url . '/role_expire/index', $query);
        $table = ee('CP/Table', [
            'lang_cols' => true,
            'sort_col' => $sort_col,
            'sort_dir' => $sort_dir,
            'class' => 'role_expire',
            'limit' => $this->per_page,
        ]);

        $vars['cp_page_title'] = lang('mr.role.title');
        $table->setColumns([
            'ct.sub.id',
            'ct.sub.last_rebill_date',
            'ct.sub.next_rebill_date' => ['sort' => false],
            'ct.sub.name',
            'ct.sub.member_id',
            'ct.sub.order_id',
            'ct.sub.status' => ['encode' => false],
            'ct.sub.manage' => [
                'type' => Table::COL_TOOLBAR,
            ],
        ]);
        return $this;
    }
}
