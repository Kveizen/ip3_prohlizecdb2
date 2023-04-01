
<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeDeletePage extends CRUDPage
{
    
    public function __construct()
    {
    }

    protected function prepare(): void
    {
        echo "ljsef";
        parent::prepare();

        $employeeId = filter_input(INPUT_POST, 'employeeId', FILTER_VALIDATE_INT);
        var_dump($employeeId);
        if (!$employeeId)
            throw new BadRequestException();

        //když poslal data
        $success = Employee::deleteByID($employeeId);
        var_dump($success);

        //přesměruj
        $this->redirect(self::ACTION_DELETE, $success);
    }

    protected function pageBody()
    {
        return "Ahoj :)";
    }

}

$page = new EmployeeDeletePage();
$page->render();

?>
