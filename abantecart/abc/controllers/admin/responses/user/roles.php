<?php

namespace abc\controllers\admin;

use abc\core\engine\AController;
use abc\core\engine\Registry;
use abc\core\lib\AJson;
use abc\models\user\Ability;
use abc\models\user\Permission;
use abc\models\user\Role;
use abc\models\user\User;

class ControllerResponsesUserRoles extends AController
{

    /** @var \Silber\Bouncer\Bouncer */
    private $arbac;

    private $userModel;

    /**
     * ControllerResponsesUserRoles constructor.
     */
    public function __construct($registry, $instance_id, $controller, $parent_controller = '')
    {
        parent::__construct($registry, $instance_id, $controller, $parent_controller = '');
        $this->arbac = $this->registry->get('bouncer');
    }

    public function createRole()
    {
        if (!$this->request->is_POST()) {
            return;
        }

        $post = $this->request->post;
        if (!isset($post['roleName'])) {
            return;
        }
        \H::df($post);

        if (isset($post['user_model'])) {
            $this->userModel = $post['user_model'];
        }

        $role = $this->arbac->role()->firstOrCreate([
            'name'        => $post['roleName'],
            'title'       => $post['roleName'],
            'user_model' => $this->userModel,
        ]);

        $this->data['response'] = false;
        if ($role) {
            $this->data['response'] = true;
        }

        $this->load->library('json');
        $this->response->setOutput(AJson::encode($this->data['response']));

    }

    public function updateRole()
    {
        if (!$this->request->is_POST()) {
            return;
        }

        if (isset($this->request->post['user_model'])) {
            $this->userModel = $this->request->post['user_model'];
        }

        $post = $this->request->post;
        if (!isset($post['role'])) {
            return;
        }
        $this->data['response'] = false;

        $role = Role::find((int)$post['role']['id']);
        if ($role) {
            $update = [
                'name'  => $post['role']['name'],
                'title' => $post['role']['title'],
                'user_model' => $this->userModel,
            ];
            $role->update($update);
            $this->data['response'] = true;
        }

        $this->load->library('json');
        $this->response->setOutput(AJson::encode($this->data['response']));
    }

    public function deleteRole()
    {
        if (!$this->request->is_POST()) {
            return;
        }

        $post = $this->request->post;
        if (!isset($post['role'])) {
            return;
        }
        $this->data['response'] = false;

        $role = Role::find((int)$post['role']['id']);
        if ($role) {
            $role->delete();
            $this->data['response'] = true;
        }
        $this->load->library('json');
        $this->response->setOutput(AJson::encode($this->data['response']));
    }

    public function getRoles()
    {
        if (isset($this->request->post['user_model'])) {
            $this->userModel = $this->request->post['user_model'];
        }

        $roles = Role::where('scope', '=', $this->registry->get('config')->get('config_store_id'))
            ->where('user_model', '=', $this->userModel)
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
        }

        $this->data['response'] = $roles;

        $this->load->library('json');
        $this->response->setOutput(AJson::encode($this->data['response']));
    }

    public function getAbilities()
    {
        if (!$this->request->is_POST()) {
            return;
        }

        if (!(int)$this->request->post['id']) {
            return;
        }

        $roleId = (int)$this->request->post['id'];

        if (isset($this->request->post['user_model'])) {
            $this->userModel = $this->request->post['user_model'];
        }

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
            $abilities = $this->prepareAbilities($abilities, $roleId);
        } else {
            $abilities = [];
        }

        $this->data['response'] = $abilities;

        $this->load->library('json');
        $this->response->setOutput(AJson::encode($this->data['response']));
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

        $permissions = $this->getPermissions($roleId);
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

    private function getPermissions($roleId)
    {
        $permissions = Permission::where('permissions.entity_id', '=', $roleId)
            ->where('permissions.entity_type', '=', 'roles')
            ->get();
        if ($permissions) {
            return $permissions->toArray();
        } else {
            return [];
        }
    }

    public function changePermissions()
    {
        $post = $this->request->post;
        $abilityNames = explode(',', $post['name']);
        $abilityIds = explode(',', $post['grouped_id']);

        $role = Role::find((int)$post['role_id']);
        if (!$role) {
            return;
        }

        foreach ($abilityNames as $key => $abilityName) {
            $ability = Ability::find((int)$abilityIds[$key]);
            if (!$ability) {
                continue;
            }
            if (!isset($post[$abilityName])) {
                continue;
            }

            if ($post[$abilityName] === '1') {
                $this->arbac->allow($role)->to($ability);
            } else {
                $this->arbac->disallow($role)->to($ability);
            }
        }

    }

}

?>
