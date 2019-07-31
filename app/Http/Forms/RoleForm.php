<?php

namespace App\Http\Forms;

use App\Models\Role;
use Grafite\FormMaker\Fields\Text;
use Grafite\FormMaker\Fields\Checkbox;
use Grafite\FormMaker\Forms\ModelForm;

class RoleForm extends ModelForm
{
    public $model = Role::class;

    public $routePrefix = 'admin.roles';

    public $buttons = [
        'save' => 'Save',
    ];

    public $columns = 2;

    public $routes = [
        'create' => '.store',
        'update' => '.update',
        'delete' => '.destroy',
    ];

    public function setSections()
    {
        return [
            [
                'label',
            ],
            'Permissions' => $this->permissionOptionKeys()
        ];
    }

    public function fields()
    {
        return array_merge([
            Text::make('label', [
                'required' => true
            ]),
        ], $this->permissionOptions());
    }

    public function permissionOptionKeys()
    {
        $options = [];
        $permissions = config('permissions');

        foreach ($permissions as $model => $action) {
            foreach ($action as $name => $label) {
                $options[] = "permissions[$model.$name]";
            }
        }

        return $options;
    }

    public function permissionOptions()
    {
        $options = [];
        $permissions = config('permissions');

        foreach ($permissions as $model => $action) {
            foreach ($action as $name => $label) {
                $options[] = Checkbox::make("permissions[$model.$name]", [
                    'label' => $label
                ]);
            }
        }

        return $options;
    }
}
