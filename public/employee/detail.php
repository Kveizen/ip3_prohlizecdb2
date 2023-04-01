<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeDetailPage extends BasePage
{
    private $room;
    private $employees;
    private $keys;

    protected function prepare(): void
    {
        parent::prepare();
        //získat data z GET
        $employeeId = filter_input(INPUT_GET, 'employeeId', FILTER_VALIDATE_INT);
        if (!$employeeId)
            throw new BadRequestException();

        //najít zaměstnance v databázi
        $this->employee = Employee::findByID($employeeId);
        if (!$this->employee)
            throw new NotFoundException();
        
        $this->room = Room::findByID($this->employee->room);

        $this->keys = Key::findByEmployee($employeeId);
    }

    protected function pageBody()
    {
        //prezentovat data
        return MustacheProvider::get()->render(
            'employeeDetail',
            ['employee' => $this->employee, 'is_at_room' => $this->room, "keys"=>$this->keys]
        );
    }

}

$page = new EmployeeDetailPage();
$page->render();

?>
