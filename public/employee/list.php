<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeesPage extends CRUDPage
{
    private $alert = [];

    public function __construct()
    {
        $this->title = "Výpis zaměstnanců";
    }

    protected function prepare(): void
    {
        parent::prepare();
        //pokud přišel výsledek, zachytím ho
        $crudResult = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT);
        $crudAction = filter_input(INPUT_GET, 'action');

        if (is_int($crudResult)) {
            $this->alert = [
                'alertClass' => $crudResult === 0 ? 'danger' : 'success'
            ];

            $message = '';
            if ($crudResult === 0)
            {
                $message = 'Operace nebyla úspěšná';
            }
            else if ($crudAction === self::ACTION_DELETE)
            {
                $message = 'Smazání proběhlo úspěšně';
            }
            else if ($crudAction === self::ACTION_INSERT)
            {
                $message = 'Zaměstnanec byl přídán úspěšně';
            }
            else if ($crudAction === self::ACTION_UPDATE)
            {
                $message = 'Úprava zaměstnance byla úspěšná';
            }

            $this->alert['message'] = $message;
        }

    }


    protected function pageBody()
    {
        $html = "";
        //zobrazit alert
        if ($this->alert) {
            $html .= MustacheProvider::get()->render('crudResult', $this->alert);
        }

        //získat data
        $employees = Employee::getAll(['name' => 'ASC']);
        //prezentovat data
        if($_SESSION['admin'] == 1)
        {
            $html .= MustacheProvider::get()->render('employeeList',['employees' => $employees, "isadmin" => true]);
        }
        else
        {
            $html .= MustacheProvider::get()->render('employeeList',['employees' => $employees, "isadmin" => false]);
        }

        return $html;
    }

}

$page = new EmployeesPage();
$page->render();

?>
