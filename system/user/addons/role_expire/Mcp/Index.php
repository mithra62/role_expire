<?php

namespace Mithra62\RoleExpire\Mcp;

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

    public $per_page = 10;

    protected $base_url = 'addons/settings/role_expire';

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

        $base_url = ee('CP/URL')->make($this->base_url . '/index', $query);
        $table = ee('CP/Table', [
            'lang_cols' => true,
            'sort_col' => $sort_col,
            'sort_dir' => $sort_dir,
            'class' => 'role_expire',
            'limit' => $this->per_page,
        ]);

        $vars['cp_page_title'] = lang('mr.role.title');
        $table->setColumns([
            're.role.id' => 'id',
            're.role.name' => 'name',
            're.role.ttl' => ['sort' => false],
            're.role.enabled' => ['encode' => false, 'sort' => false],
            're.role.manage' => [
                'type' => Table::COL_TOOLBAR,
            ],
        ]);

        $table->setNoResultsText(sprintf(lang('no_found'), lang('ct.re.expires')));

        $roles = ee('Model')
            ->get('ee:Role')
            ->filter('role_id', '!=', 1);

        $page = ((int)ee('Request')->get('page')) ?: 1;
        $offset = ($page - 1) * $this->per_page; // Offset is 0 indexed

        // Handle Pagination
        $totalRoles = $roles->count();

        $roles->limit($this->per_page)
            ->offset($offset);

        $data = [];
        $sort_map = [
            're.role.id' => 'role_id',
            're.role.name' => 'name',
        ];

        $roles->order($sort_map[$sort_col], $sort_dir);
        foreach ($roles->all() as $role) {
            $url = $this->url( 'edit/' . $role->getId());
            $data[] = [
                [
                    'content' => $role->getId(),
                    'href' => $url,
                ],
                $role->name,
                ee('role_expire:RolesService')->checkTtl($role),
                "<span class='" . ee('role_expire:RolesService')->getStatusCss($role) . "'>" . lang(ee('role_expire:RolesService')->getEnabled($role)) . '</span>',
                ['toolbar_items' => [
                    'edit' => [
                        'href' => $url,
                        'title' => lang('edit'),
                    ],
                ]],
            ];
        }

        $table->setData($data);

        $vars['pagination'] = ee('CP/Pagination', $totalRoles)
            ->perPage($this->per_page)
            ->currentPage($page)
            ->render($base_url);
        $vars['table'] = $table->viewData($base_url);
        $vars['base_url'] = $base_url;

        $this->setBody('index', $vars);
        return $this;
    }
}
