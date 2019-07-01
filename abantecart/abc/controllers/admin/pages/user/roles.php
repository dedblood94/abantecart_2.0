<?php

namespace abc\controllers\admin;

use abc\core\engine\AController;
use abc\models\customer\Customer;
use abc\models\user\Role;
use abc\models\user\User;
use Laracasts\Utilities\JavaScript\PHPToJavaScriptTransformer;

class ControllerPagesUserRoles extends AController
{
    public function main()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);
        $this->document->setTitle($this->language->get('heading_title'));
        $this->view->assign('error_warning', $this->error['warning']);
        $this->view->assign('success', $this->session->data['success']);
        if (isset($this->session->data['success'])) {
            unset($this->session->data['success']);
        }

        $this->document->initBreadcrumb([
            'href'      => $this->html->getSecureURL('index/home'),
            'text'      => $this->language->get('text_home'),
            'separator' => false,
        ]);
        if (isset($this->request->get['customer'])) {
            $this->document->addBreadcrumb([
                'href'      => $this->html->getSecureURL('user/roles'),
                'text'      => $this->language->get('heading_title_customers'),
                'separator' => ' :: ',
                'current'   => true,
            ]);
        } else {
            $this->document->addBreadcrumb([
                'href'      => $this->html->getSecureURL('user/roles'),
                'text'      => $this->language->get('heading_title_user'),
                'separator' => ' :: ',
                'current'   => true,
            ]);
        }

        $userModel = User::class;
        if (isset($this->request->get['customer'])) {
            $userModel = Customer::class;
        }

        $roles = Role::where('scope', '=', $this->registry->get('config')->get('config_store_id'))
            ->where('user_model', '=', $userModel)
            ->get()
            ->toArray();

        foreach ($roles as &$role) {
            $role['edit'] = false;
            $query = $this->db->table('assigned_roles')
                ->select($this->db->raw('count(id) as user_count'))
                ->where('role_id', '=', $role['id'])
                ->where('entity_type', '=', 'User')
                ->get()
                ->first();
            if ($query) {
                $role['user_count'] = $query->user_count;
            }
            $role['selected'] = false;
        }


        $this->data['role'] = [
            'get_roles_url'           => $this->html->getSecureURL('r/user/roles/getRoles'),
            'create_role_url'         => $this->html->getSecureURL('r/user/roles/createRole'),
            'update_role_url'         => $this->html->getSecureURL('r/user/roles/updateRole'),
            'delete_role_url'         => $this->html->getSecureURL('r/user/roles/deleteRole'),
            'get_abilities_url'       => $this->html->getSecureURL('r/user/roles/getAbilities'),
            'change_permissions_url'  => $this->html->getSecureURL('r/user/roles/changePermissions'),
            'user_model'              => $userModel,
            'roles'                   => $roles,
            'roles_table_headers'     => [
                [
                    'text'     => $this->language->get('title_role'),
                    'align'    => 'left',
                    'sortable' => true,
                    'value'    => 'title',
                ],
                [
                    'text'     => $this->language->get('title_user_count'),
                    'align'    => 'left',
                    'sortable' => true,
                    'value'    => 'user_count',
                ],
                [
                    'text'     => $this->language->get('title_operation'),
                    'align'    => 'center',
                    'sortable' => false,
                    'value'    => '',
                ],
            ],
            'abilities_table_headers' => [
                [
                    'title'        => $this->language->get('title_ability'),
                    'align'        => 'left',
                    'sortable'     => false,
                    'property'     => 'entity_type',
                    'filterable'   => true,
                ],
                [
                    'title'    => $this->language->get('title_read'),
                    'align'    => 'center',
                    'sortable' => false,
                    'property' => 'read',
                ],
                [
                    'title'    => $this->language->get('title_write'),
                    'align'    => 'center',
                    'sortable' => false,
                    'property' => 'write',
                ],
                [
                    'title'    => $this->language->get('title_delete'),
                    'align'    => 'center',
                    'sortable' => false,
                    'property' => 'delete',
                ],
            ],
        ];

        //Put Php array to Javascript Object - abc.roles
        $transformer = new PHPToJavaScriptTransformer($this->document, 'abc');
        $transformer->put(['role' => $this->data['role']]);

        $this->view->batchAssign($this->data);
        $this->processTemplate('pages/user/roles.tpl');

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }
}
