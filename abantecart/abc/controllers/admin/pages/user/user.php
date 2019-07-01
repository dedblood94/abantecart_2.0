<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2018 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/

namespace abc\controllers\admin;

use abc\core\ABC;
use abc\core\engine\AController;
use abc\core\engine\AForm;
use abc\models\user\Ability;
use abc\models\user\AssignedRole;
use abc\models\user\Permission;
use abc\models\user\Role;
use abc\models\user\User;
use H;
use Laracasts\Utilities\JavaScript\PHPToJavaScriptTransformer;

class ControllerPagesUserUser extends AController
{
    public $data = [];
    public $error = [];
    /** @var string $userModel */
    private $userModel;
    protected $fields = ['username', 'firstname', 'lastname', 'email', 'user_group_id', 'status'];

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
        $this->document->addBreadcrumb([
            'href'      => $this->html->getSecureURL('user/user'),
            'text'      => $this->language->get('heading_title'),
            'separator' => ' :: ',
            'current'   => true,
        ]);

        $grid_settings = [
            'table_id'     => 'user_grid',
            'url'          => $this->html->getSecureURL('listing_grid/user'),
            'editurl'      => $this->html->getSecureURL('listing_grid/user/update'),
            'update_field' => $this->html->getSecureURL('listing_grid/user/update_field'),
            'sortname'     => 'username',
            'sortorder'    => 'asc',
            'actions'      => [
                'edit'   => [
                    'text' => $this->language->get('text_edit'),
                    'href' => $this->html->getSecureURL('user/user/update', '&user_id=%ID%'),
                ],
                'save'   => [
                    'text' => $this->language->get('button_save'),
                ],
                'delete' => [
                    'text' => $this->language->get('button_delete'),
                ],
            ],
        ];

        $grid_settings['colNames'] = [
            $this->language->get('column_username'),
            $this->language->get('column_group'),
            $this->language->get('column_status'),
            $this->language->get('column_date_added'),
        ];
        $grid_settings['colModel'] = [
            [
                'name'  => 'username',
                'index' => 'username',
                'width' => 300,
                'align' => 'left',
            ],
            [
                'name'   => 'user_group_id',
                'index'  => 'user_group_id',
                'width'  => 120,
                'align'  => 'left',
                'search' => false,
            ],
            [
                'name'   => 'status',
                'index'  => 'status',
                'width'  => 130,
                'align'  => 'center',
                'search' => false,
            ],
            [
                'name'   => 'date_added',
                'index'  => 'date_added',
                'width'  => 100,
                'align'  => 'center',
                'search' => false,
            ],
        ];

        $statuses = [
            '' => $this->language->get('text_select_status'),
            1  => $this->language->get('text_enabled'),
            0  => $this->language->get('text_disabled'),
        ];

        $this->loadModel('user/user_group');
        $user_groups = ['' => $this->language->get('text_select_group'),];
        $results = $this->model_user_user_group->getUserGroups();
        foreach ($results as $r) {
            $user_groups[$r['user_group_id']] = $r['name'];
        }

        $form = new AForm();
        $form->setForm([
            'form_name' => 'user_grid_search',
        ]);

        $grid_search_form = [];
        $grid_search_form['id'] = 'user_grid_search';
        $grid_search_form['form_open'] = $form->getFieldHtml([
            'type'   => 'form',
            'name'   => 'user_grid_search',
            'action' => '',
        ]);
        $grid_search_form['submit'] = $form->getFieldHtml([
            'type'  => 'button',
            'name'  => 'submit',
            'text'  => $this->language->get('button_go'),
            'style' => 'button1',
        ]);
        $grid_search_form['reset'] = $form->getFieldHtml([
            'type'  => 'button',
            'name'  => 'reset',
            'text'  => $this->language->get('button_reset'),
            'style' => 'button2',
        ]);
        $grid_search_form['fields']['status'] = $form->getFieldHtml([
            'type'    => 'selectbox',
            'name'    => 'status',
            'options' => $statuses,
        ]);
        $grid_search_form['fields']['group'] = $form->getFieldHtml([
            'type'    => 'selectbox',
            'name'    => 'user_group_id',
            'options' => $user_groups,
        ]);

        $grid_settings['search_form'] = true;

        $grid = $this->dispatch('common/listing_grid', [$grid_settings]);
        $this->view->assign('listing_grid', $grid->dispatchGetOutput());
        $this->view->assign('search_form', $grid_search_form);

        $this->view->assign('insert', $this->html->getSecureURL('user/user/insert'));
        $this->view->assign('help_url', $this->gen_help_url('user_listing'));

        $this->processTemplate('pages/user/user_list.tpl');

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    public function insert()
    {

        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('user/user');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->loadModel('user/user');
        if ($this->request->is_POST() && $this->validateForm()) {
            $user_id = $this->model_user_user->addUser($this->request->post);
            if ($user_id && isset($this->request->post['roles'])) {
                User::updateRoles($user_id, $this->request->post['roles']);
            }
            $this->session->data['success'] = $this->language->get('text_success');
            abc_redirect($this->html->getSecureURL('user/user/update', '&user_id='.$user_id));
        }
        $this->getForm();

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    public function update()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->loadLanguage('user/user');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->loadModel('user/user');

        $this->getTabs($this->request->get['user_id'], 'general');

        $this->view->assign('success', $this->session->data['success']);
        if (isset($this->session->data['success'])) {
            unset($this->session->data['success']);
        }

        if ($this->request->is_POST() && $this->validateForm()) {
            $this->model_user_user->editUser($this->request->get['user_id'], $this->request->post);
            if (isset($this->request->post['roles'])) {
                User::updateRoles($this->request->get['user_id'], $this->request->post['roles']);
            }
            $this->session->data['success'] = $this->language->get('text_success');
            abc_redirect($this->html->getSecureURL('user/user/update', '&user_id='.$this->request->get['user_id']));
        }

        $this->getForm();

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    protected function getForm()
    {

        $this->data['error'] = $this->error;

        $this->document->initBreadcrumb([
            'href'      => $this->html->getSecureURL('index/home'),
            'text'      => $this->language->get('text_home'),
            'separator' => false,
        ]);
        $this->document->addBreadcrumb([
            'href'      => $this->html->getSecureURL('user/user'),
            'text'      => $this->language->get('heading_title'),
            'separator' => ' :: ',
        ]);

        $this->data['cancel'] = $this->html->getSecureURL('user/user');

        if (isset($this->request->get['user_id'])) {
            $user_id = $this->request->get['user_id'];
            $this->data['user_id'] = $user_id;
            $user_info = $this->model_user_user->getUser($user_id);
        } else {
            $user_id = 0;
        }

        foreach ($this->fields as $f) {
            if (isset($user_info)) {
                $this->data[$f] = $user_info[$f];
            } elseif (isset($this->request->post[$f])) {
                $this->data[$f] = $this->request->post[$f];
            } else {
                $this->data[$f] = '';
            }
        }

        if (!$user_id) {
            $this->data['action'] = $this->html->getSecureURL('user/user/insert');
            $this->data['heading_title'] =
                $this->language->get('text_insert').'&nbsp;'.$this->language->get('text_user');
            $this->data['update'] = '';
            $form = new AForm('ST');
        } else {
            $this->data['action'] = $this->html->getSecureURL('user/user/update', '&user_id='.$user_id);
            $this->data['heading_title'] =
                $this->language->get('text_edit').'&nbsp;'.$this->language->get('text_user').' - '
                .$this->data['username'];
            $this->data['update'] = $this->html->getSecureURL('listing_grid/user/update_field', '&id='.$user_id);
            $form = new AForm('HS');
            $this->data['edit_im_url'] = $this->html->getSecureURL('user/user/im', '&user_id='.$user_id);

        }

        $this->document->addBreadcrumb([
            'href'      => $this->data['action'],
            'text'      => $this->data['heading_title'],
            'separator' => ' :: ',
            'current'   => true,
        ]);

        $form->setForm([
            'form_name' => 'cgFrm',
            'update'    => $this->data['update'],
        ]);

        $this->data['form']['id'] = 'cgFrm';
        $this->data['form']['form_open'] = $form->getFieldHtml([
            'type'   => 'form',
            'name'   => 'cgFrm',
            'action' => $this->data['action'],
            'attr'   => 'data-confirm-exit="true" class="aform form-horizontal"',
        ]);
        $this->data['form']['submit'] = $form->getFieldHtml([
            'type'  => 'button',
            'name'  => 'submit',
            'text'  => $this->language->get('button_save'),
            'style' => 'button1',
        ]);
        $this->data['form']['cancel'] = $form->getFieldHtml([
            'type'  => 'button',
            'name'  => 'cancel',
            'text'  => $this->language->get('button_cancel'),
            'style' => 'button2',
        ]);

        $this->data['form']['fields']['status'] = $form->getFieldHtml([
            'type'  => 'checkbox',
            'name'  => 'status',
            'value' => $this->data['status'],
            'style' => 'btn_switch',
        ]);

        $input = ['username', 'firstname', 'lastname', 'password'];
        foreach ($input as $f) {
            $this->data['form']['fields'][$f] = $form->getFieldHtml([
                'type'     => ($f == 'password' ? 'passwordset' : 'input'),
                'name'     => $f,
                'value'    => $this->data[$f],
                'required' => true,
                'attr'     => (in_array($f, ['password', 'password_confirm']) ? 'class="no-save"' : ''),
                'style'    => ($f == 'password' ? 'medium-field' : ''),
            ]);
        }

        $arRoles = Role::where('user_model', '=', User::class)
            ->get()
            ->toArray();
        $roles = [];
        foreach ($arRoles as $role) {
            $roles[$role['id']] = $role['title'];
        }
        $this->data['roles'] = [];

        if ($user_id) {
            $assignedRoles = AssignedRole::select(['role_id'])
                ->where('entity_id', '=', $user_id)
                ->where('entity_type', '=', 'User')
                ->get();
            if ($assignedRoles) {
                foreach ($assignedRoles as $assignedRole) {
                    $this->data['roles'][] = $assignedRole->role_id;
                }
            }
        }

        $this->data['form']['fields']['roles'] = $form->getFieldHtml([
            'type'     => 'checkboxgroup',
            'name'     => 'roles[]',
            'value'    => $this->data['roles'],
            'options'  => $roles,
            'style'    => 'chosen',
            'required' => true,
        ]);

        //forbid to downgrade permissions
        // if user admin and only one
        $attr = '';
        //user cannot to change group for himself
        if ($user_id == $this->user->getId()) {
            $attr = ' disabled="disabled" ';
        }
        //non-admin cannot too
        if ($user_id && $this->user->getUserGroupId() != 1) {
            $attr = ' disabled="disabled" ';
        }

        $this->loadModel('user/user_group');
        $user_groups = [];
        $results = $this->model_user_user_group->getUserGroups();

        foreach ($results as $r) {
            //do not show top-admin-group for non-admin for new user form
            if (!$user_id && $this->user->getUserGroupId() != 1 && $r['user_group_id'] == 1) {
                continue;
            }
            $user_groups[$r['user_group_id']] = $r['name'];
        }

        $this->data['form']['fields']['user_group'] = $form->getFieldHtml([
            'type'    => 'selectbox',
            'name'    => 'user_group_id',
            'value'   => $this->data['user_group_id'],
            'options' => $user_groups,
            'attr'    => $attr,
        ]);

        $this->data['form']['fields']['email'] = $form->getFieldHtml([
            'type'     => 'input',
            'name'     => 'email',
            'value'    => $this->data['email'],
            'required' => true,
        ]);

        $this->view->assign('help_url', $this->gen_help_url('user_edit'));
        $this->view->batchAssign($this->data);

        $this->processTemplate('/pages/user/user_form.tpl');
    }

    public function im()
    {
        //init controller data
        $this->extensions->hk_InitData($this, __FUNCTION__);

        if (isset($this->request->get['user_id'])) {
            $user_id = $this->request->get['user_id'];
        } else {
            $user_id = 0;
        }
        if (!$user_id) {
            abc_redirect($this->html->getSecureURL('user/user'));
        }

        $this->loadLanguage('common/im');
        $this->loadLanguage('user/user');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->loadModel('user/user');
        $user_info = $this->model_user_user->getUser($user_id);

        $this->view->assign('form_store_switch', $this->html->getStoreSwitcher());
        $this->data['edit_profile_url'] = $this->html->getSecureURL('user/user/update', '&user_id='.$user_id);

        $this->view->assign('success', $this->session->data['success']);
        if (isset($this->session->data['success'])) {
            unset($this->session->data['success']);
        }

        $this->document->initBreadcrumb([
            'href'      => $this->html->getSecureURL('index/home'),
            'text'      => $this->language->get('text_home'),
            'separator' => false,
        ]);
        $this->document->addBreadcrumb([
            'href'      => $this->html->getSecureURL('user/user'),
            'text'      => $this->language->get('heading_title'),
            'separator' => ' :: ',
        ]);

        $this->document->addBreadcrumb([
            'href'      => $this->html->getSecureURL('user/user/update', '&user='.$user_id),
            'text'      => sprintf($this->language->get('text_notification_for', 'common/im'), $user_info['username']),
            'separator' => ' :: ',
            'current'   => true,
        ]);

        $this->data['cancel'] = $this->html->getSecureURL('user/user');

        $this->loadLanguage('common/im');
        $protocols = $this->im->getProtocols();

        $sendpoints = array_merge(array_keys($this->im->sendpoints), array_keys($this->im->admin_sendpoints));

        foreach ($sendpoints as $sendpoint) {
            $ims = $this->im->getUserIMs($user_id, $this->session->data['current_store_id']);
            $imsettings = array_merge((array)$ims['storefront'][$sendpoint], (array)$ims['admin'][$sendpoint]);
            $values = [];

            foreach ($imsettings as $row) {
                if ($row['uri'] && in_array($row['protocol'], $protocols)) {
                    $values[$row['protocol']] = $row['protocol'];
                }
            }
            //send notification id present for admin => 1
            if (!empty($this->im->sendpoints[$sendpoint][1]) || !empty($this->im->admin_sendpoints[$sendpoint][1])) {
                $this->data['sendpoints'][$sendpoint] = [
                    'id'     => $sendpoint,
                    'text'   => $this->language->get('im_sendpoint_name_'.H::preformatTextID($sendpoint)),
                    'values' => $values,
                ];
            }
        }

        $this->data['im_settings_url'] = $this->html->getSecureURL('user/user_ims/settings', '&user_id='.$user_id);
        $this->data['text_change_im_addresses'] = $this->language->get('text_change_im_addresses');

        $this->view->assign('help_url', $this->gen_help_url('user_edit'));
        $this->view->batchAssign($this->data);

        $this->processTemplate('/pages/user/user_im.tpl');

        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    protected function validateForm()
    {
        if (!$this->user->canModify('user/user')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (mb_strlen($this->request->post['username']) < 2 || mb_strlen($this->request->post['username']) > 20) {
            $this->error['username'] = $this->language->get('error_username');
        }

        if (mb_strlen($this->request->post['firstname']) < 2 || mb_strlen($this->request->post['firstname']) > 32) {
            $this->error['firstname'] = $this->language->get('error_firstname');
        }

        if (mb_strlen($this->request->post['lastname']) < 2 || mb_strlen($this->request->post['lastname']) > 32) {
            $this->error['lastname'] = $this->language->get('error_lastname');
        }

        if (mb_strlen($this->request->post['email']) > 96
            || !preg_match(ABC::env('EMAIL_REGEX_PATTERN'), $this->request->post['email'])) {
            $this->error['email'] = $this->language->get('error_email');
        }

        if (($this->request->post['password']) || (!isset($this->request->get['user_id']))) {
            if (mb_strlen($this->request->post['password']) < 4) {
                $this->error['password'] = $this->language->get('error_password');
            }

            if ($this->request->post['password'] != $this->request->post['password_confirm']) {
                $this->error['password'] = $this->language->get('error_confirm');
            }
        }

        $user_info = $this->model_user_user->getUser($this->request->get['user_id']);
        if ($this->request->post['user_group_id']) {
            if ($user_info['user_group_id'] != $this->request->post['user_group_id']) {
                if ( //cannot to change group for yourself
                    $this->request->get['id'] == $this->user->getId()
                    //or current user is not admin
                    || $this->user->getUserGroupId() != 1) {
                    $this->error['user_group'] = $this->language->get('error_user_group');
                }
            }
        } elseif (!$user_info) {
            $this->error['user_group'] = $this->language->get('error_user_group');
        }

        $this->extensions->hk_ValidateData($this);

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    private function getTabs(int $user_id, $active = '')
    {
        $this->data['tabs']['general'] = [
            'href'   => $this->html->getSecureURL('user/user/update', '&user_id='.$user_id),
            'text'   => $this->language->get('tab_user_detail'),
            'active' => ($active === 'general'),
            'sort_order' => 0,
        ];
        $this->data['tabs']['permissions'] = [
            'href'   => $this->html->getSecureURL('user/user/permissions', '&user_id='.$user_id),
            'text'   => $this->language->get('tab_user_permissions'),
            'active' => ($active === 'permissions'),
            'sort_order' => 10,
        ];

        $obj = $this->dispatch('responses/common/tabs', [
                'user/user',
                ['tabs' => $this->data['tabs']]
            ]
        );
        $this->data['tabs'] = $obj->dispatchGetOutput();
    }

    public function permissions()
    {
        $userId = (int)$this->request->get['user_id'];

        $user = User::find($userId);

        $this->loadLanguage('user/user');
        $this->data['heading_title'] =
            $this->language->get('text_edit').'&nbsp;'.$this->language->get('text_user').' - '
            .$user->username;

        $this->document->initBreadcrumb([
            'href'      => $this->html->getSecureURL('index/home'),
            'text'      => $this->language->get('text_home'),
            'separator' => false,
        ]);
        $this->document->addBreadcrumb([
            'href'      => $this->html->getSecureURL('user/user'),
            'text'      => $this->data['heading_title'],
            'separator' => ' :: ',
            'current'   => true
        ]);


        $this->extensions->hk_InitData($this, __FUNCTION__);

        $this->userModel = User::class;


        $this->getTabs($userId, 'permissions');

        $this->data['abilities'] = $this->getAbilities();

        $this->data['permissions'] = [
            'change_permissions_url'  => $this->html->getSecureURL('r/user/user_permissions/changePermissions'),
            'user_model'              => $this->userModel,
            'user_id'                 => $userId,
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
            'abilities' => $this->data['abilities'],
        ];

        //Put Php array to Javascript Object - abc.roles
        $transformer = new PHPToJavaScriptTransformer($this->document, 'abc');
        $transformer->put(['permissions' => $this->data['permissions']]);

        $this->view->batchAssign($this->data);
        $this->processTemplate('/pages/user/user_permissions.tpl');
        //update controller data
        $this->extensions->hk_UpdateData($this, __FUNCTION__);
    }

    public function getAbilities()
    {
        $abilities = Ability::select([
            $this->db->raw('GROUP_CONCAT('.$this->db->table_name('abilities').'.id) as grouped_id'),
            $this->db->raw('GROUP_CONCAT('.$this->db->table_name('abilities').'.name) as name'),
            $this->db->raw('GROUP_CONCAT('.$this->db->table_name('abilities').'.parent_id) as parent_id'),
            $this->db->raw('GROUP_CONCAT('.$this->db->table_name('abilities').'.title) as title'),
            $this->db->raw('GROUP_CONCAT('.$this->db->table_name('abilities').'.user_model) as user_model'),
            'abilities.entity_type',
        ])
            ->whereNull('entity_id')
            ->where('user_model', '=', $this->userModel)
            ->groupBy('abilities.entity_type')
            ->orderBy('id');

        $abilities = $abilities->get();

        if ($abilities) {
            $abilities = $abilities->toArray();
            $abilities = $this->prepareAbilities($abilities);
        } else {
            $abilities = [];
        }

        return $abilities;
    }

    private function prepareAbilities($abilities = [], $roleId = 0)
    {
        $result = [];

        foreach ($abilities as $key => $ability) {
            $ability['role_id'] = $roleId;
            if ($ability['parent_id']) {
                $parent_key = array_search($ability['parent_id'], array_column($result, 'grouped_id'), true);
                if ($parent_key !== false) {
                    $result[$parent_key]['_children'][] = $ability;
                }
            } else {
                $result[] = $ability;
            }
        }
        $userId = (int)$this->request->get['user_id'];

        $permissions = $this->getPermissions($userId);
        $result = $this->extractAbilitiesFromGroups($result, $permissions);

        return $result;
    }

    private function extractAbilitiesFromGroups($abilities = [], $permissions = [])
    {
        foreach ($abilities as &$ability) {
            $abilityIds = explode(',', $ability['grouped_id']);
            foreach (explode(',', $ability['name']) as $key => $ability_name) {
                $ability[$ability_name] = false;
                if (isset($abilityIds[$key])) {
                    $permissionKey = array_search($abilityIds[$key], array_column($permissions, 'ability_id'), false);
                    if ($permissionKey !== false && $permissions[$permissionKey]['forbidden'] === 0) {
                        $ability[$ability_name] = true;
                    }
                }
            }
            if (is_array($ability['_children'])) {
                $ability['_children'] = $this->extractAbilitiesFromGroups($ability['_children'], $permissions);
            }
        }
        return $abilities;
    }

    private function getPermissions($userId)
    {
        $permissions = Permission::where('permissions.entity_id', '=', $userId)
            ->where('permissions.entity_type', '=', 'User')
            ->get();
        if ($permissions) {
            return $permissions->toArray();
        } else {
            return [];
        }
    }

}
