<?php

namespace Lararole\Containers;

use Lararole\Models\Module;

class ModuleServiceContainer
{
    public function all()
    {
        return Module::all();
    }

    public function find($id)
    {
        return Module::find($id);
    }

    public function root()
    {
        return Module::root()->get();
    }

    public function leaf()
    {
        return Module::leaf()->get();
    }
}
