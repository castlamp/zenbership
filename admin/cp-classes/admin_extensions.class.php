<?php

/**
 *
 *
 * @author  j-belelieu
 * @date    3/28/2015
 */

class admin_extensions extends admin {

    protected $settings;

    protected $employee;

    protected $extension;

    protected $path;

    protected $proceed = false;

    protected $task_id;

    protected $extensionObj;

    protected $activeTask = array();

    /**
     * @param $extension
     * @param $employee
     */
    public function __construct($extension, $employee)
    {
        $this->extension = $extension;

        define('ZEN_CUS_EXTENSION', $extension);

        $this->path = PP_PATH . "/custom/admin_extensions/" . $extension;

        if (file_exists($this->path . '/ExtensionObject.php')) {
            require $this->path . '/ExtensionObject.php';
            $this->extensionObj = new ExtensionObject;
        }

        $file = $this->path . '/package.php';

        if (file_exists($file)) {
            $this->settings = require $file;

            $this->employee = $employee;

            $check = $this->check_permissions($this->settings->permission, $employee);
            if ($check != '1') {
                $this->show_no_permissions();
            } else {
                $this->proceed = true;
            }
        }
    }


    /**
     * @param string $id
     *
     * @return string
     */
    public function formatFields($data = array())
    {
        $fields = '';

        foreach ($this->settings['fields'] as $key => $fData) {

            if ($fData['required']) {
                $class = 'req';
            } else {
                $class = '';
            }

            $val = (! empty($data[$key])) ? $data[$key] : '';

            $fields .= <<<qq
        <div class="field">
            <label class="top">{$fData['display']}</label>
            <div class="field_entry_top">
qq;

            if ($fData['type'] == 'bool') {

                if (! empty($val)) {
                    $checked = 'checked';
                    $checkedNo = '';
                } else {
                    $checked = '';
                    $checkedNo = 'checked';
                }
                $fields .= <<<qq
                <input type="radio" name="{$key}" value="1" checked="{$checked}" /> Yes <input type="radio" name="{$key}" value="0" checked="{$checkedNo}" /> No
qq;
            }
            else if ($fData['type'] == 'date') {
                $fields .= <<<qq
qq;
                $fields .= $this->datepicker($key, $val, '0', '250', '', '', '1');
            }
            else {
                $fields .= <<<qq
                <input type="text" name="{$key}" id="{$key}" value="{$val}" style="width:100%;" class="$class" />
qq;
            }


            $fields .= <<<qq
            </div>
        </div>
qq;


        }

        return $fields;
    }



    public function header($type)
    {
        $val = ($type == 'edit') ? 1 : 0;

        echo <<<qq
<script type="text/javascript">
    $.ctrl('S', function () {
        return json_add('custom:{$this->extension}', '{$_POST['id']}', '{$val}', 'popupform');
    });
</script>

<form action="" method="post" id="popupform"
      onsubmit="return json_add('custom:{$this->extension}','{$_POST['id']}','{$val}','popupform');">

    <div id="popupsave">
        <input type="hidden" name="dud_quick_add" value="1" />
        <input type="submit" value="Save" class="save" />
    </div>

    <h1>Edit</h1>

    <div class="pad24t popupbody">
qq;
    }


    public function footer($type)
    {
        echo <<<qq
    </div>

</form>
qq;
    }


    /**
     * @param $task
     * @param $space
     *
     * @return bool
     */
    public function methodExists($task, $space)
    {
        if (file_exists($this->path . '/' . $space . '/' . $task . '.php')) return true;

        return false;
    }


    /**
     * @param        $task
     * @param string $space
     * @param array  $input
     *
     * @return string
     */
    public function runTask($task, $space = '', $input = array())
    {
        if (empty($space)) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $space = 'func';
            } else {
                $space = 'views';
            }
        }

        try {

            ob_start();
            $extension = $this->extension;
            $settings = $this->settings;
            $obj = $this;
            $rendered = $this->formatFields($_POST);
            include $this->path . '/' . $space . '/' . $task . '.php';
            $content = ob_get_contents();
            ob_end_clean();

            return $content;

        } catch (Exception $e) {

            return "Error running extension: " . $e->getMessage();

        }
    }



    public function startAndConfirm()
    {
        if (empty($_POST['ext'])) return false;

        if ($_POST['edit'] == '1') {
            $type = 'edit';
        }
        else {
            $type = 'add';
        }

        $task = $_POST['ext'] . '-' . $type;

        $this->task_id  = $this->start_task($task, 'staff', $_POST['id'], $this->employee['username']);

        $error = false;

        $fields = '`' . implode('`,`', array_keys($this->settings['fields'])) . '`';

        $values = '';
        $updateValues = '';

        foreach ($this->settings['fields'] as $key => $crap) {
            if ($crap['required'] && empty($_POST[$key])) {
                $error = true;
                break;
            }

            $values .= ",'" . $this->mysql_cleans($_POST[$key]) . "'";

            $updateValues = ",`" . $this->mysql_cleans($key) . "`='" . $this->mysql_cleans($_POST[$key]) . "'";
        }

        $this->activeTask = array(
            'id' => $_POST['id'],
            'fields' => $fields,
            'values' => substr($values, 1),
            'updateValues' => substr($updateValues, 1),
            'error' => $error,
            'task' => $task,
            'task_id' => $this->task_id,
            'type' => $type,
        );

        return $this->activeTask;
    }


    public function endAndExecuteTask()
    {
        if ($this->activeTask['type'] == 'add') {
            $q1 = $this->insert("
                INSERT INTO " . $this->settings['table'] . " (" . $this->activeTask['fields'] . ")
                VALUES (" . ltrim($this->activeTask['values'], ',') . ")
            ");
        }
        else if ($this->activeTask['type'] == 'edit') {
            $q1 = $this->insert("
                UPDATE " . $this->settings['table'] . "
                SET " . $this->activeTask['updateValues'] . "
                WHERE `id`='" . $this->activeTask['id'] . "'
                LIMIT 1
            ");
        }

        $task = $this->end_task($this->task_id, '1');
    }

} 