<?php
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeUpdatePage extends CRUDPage
{
    private ?Employee $employee;
    private ?array $errors = [];
    private int $state;
    private $keys;
    private $rooms;

    protected function prepare(): void
    {
        parent::prepare();
        $this->findState();
        $this->title = "Upravit zaměstnance";

        //když chce formulář
        if ($this->state === self::STATE_FORM_REQUESTED)
        {
            $employeeId = filter_input(INPUT_GET, 'employeeId', FILTER_VALIDATE_INT);
            if (!$employeeId)
                throw new BadRequestException();

            //jdi dál
            $this->employee = Employee::findByID($employeeId);
            if (!$this->employee)
                throw new NotFoundException();

            $stmt = PDOProvider::get()->prepare("SELECT `key`.key_id AS key_id, `key`.room AS room, `room`.name AS room_name FROM `key` INNER JOIN `room` ON `key`.employee = :employeeId AND `key`.room = `room`.room_id ORDER BY room ASC");
            $stmt->execute(['employeeId' => $employeeId]);
            //$this->keys = $stmt->fetchAll();

            $stmtRoom = PDOProvider::get()->prepare("SELECT room_id, no, name FROM room ORDER BY no ASC");
            $stmtRoom->execute([]);

            $keysAvailable = $key = $stmt->fetch();
            while ($room = $stmtRoom->fetch())
            {
                $active = false;
                if($keysAvailable && $key->room == $room->room_id){
                    $active = true;
                    $keysAvailable = $key = $stmt->fetch();
                }
                $this->rooms[] = [
                    'room_id' => $room->room_id,
                    'no' => $room->no,
                    'name' => $room->name,
                    'isActive' => $active,
                    'selected' => $room->room_id == $this->employee->room
                ];
            }
        }

        //když poslal data
        elseif($this->state === self::STATE_DATA_SENT) {
            //načti je
            $this->employee = Employee::readPost();
            $this->keys = filter_input(INPUT_POST, 'keys',FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

            //zkontroluj je, jinak formulář
            $this->errors = [];
            $isOk = $this->employee->validate($this->errors);
            if (!$isOk)
            {
                $this->state = self::STATE_FORM_REQUESTED;
            }
            else
            {
                $success = $this->employee->update();

                //přesměruj
                $this->redirect(self::ACTION_UPDATE, $success);
            }
        }
    }

    protected function pageBody()
    {
        return MustacheProvider::get()->render(
            'employeeForm',
            [
                'employee' => $this->employee,
                'errors' => $this->errors,
                'rooms' => $this->rooms,
                'is_at_room' => $this->room, 
                "keys"=>$this->keys,
                'header' => $this->title.' '.$this->employee->name.' '.$this->employee->surname
            ]
        );
    }

    private function findState() : void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
            $this->state = self::STATE_DATA_SENT;
        else
            $this->state = self::STATE_FORM_REQUESTED;
    }

}

$page = new EmployeeUpdatePage();
$page->render();

?>