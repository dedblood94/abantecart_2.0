<?php
namespace abc\controllers\admin;

use abc\core\engine\AController;
use abc\models\user\Ability;
use abc\models\user\User;

class ControllerResponsesUserUserPermissions extends AController
{
    /** @var \Silber\Bouncer\Bouncer */
    private $arbac;


    /**
     * ControllerResponsesUserRoles constructor.
     */
    public function __construct($registry, $instance_id, $controller, $parent_controller = '')
    {
        parent::__construct($registry, $instance_id, $controller, $parent_controller = '');
        $this->arbac = $this->registry->get('bouncer');
    }

    public function changePermissions()
    {
        $post = $this->request->post;
        $abilityNames = explode(',', $post['name']);
        $abilityIds = explode(',', $post['grouped_id']);

        $user = User::find((int)$post['user_id']);
        if (!$user) {
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
                $this->arbac->allow($user)->to($ability);
            } else {
                $this->arbac->disallow($user)->to($ability);
            }
        }

    }

}
