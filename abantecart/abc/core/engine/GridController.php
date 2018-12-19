<?php
/**
 * Created by PhpStorm.
 * User: Denis Vagner
 * Date: 18/12/2018
 * Time: 14:40
 */

namespace abc\core\engine;


abstract class GridController extends AController {

    public function main()
    {
        if ($this->request->is_GET()) {
            $this->get();
        }
        if ($this->request->is_POST()) {
            $this->insert();
        }

        if ($this->request->is_PUT()) {
            $this->update();
        }

        if ($this->request->is_DELETE()) {
            $this->delete();
        }
    }

    abstract public function get();

    abstract public function insert();

    abstract public function update();

    abstract public function delete();

}