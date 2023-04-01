<?php

//namespace models;

class Employee
{
    public const DB_TABLE = "employee";

    public ?int $employee_id;
    public ?string $name;
    public ?string $surname;
    public ?string $job;
    public ?int $wage;
    public ?int $room;
    public ?string $login;
    public ?string $password;
    public ?int $admin;

    /**
     * @param int|null $room_id
     * @param string|null $name
     * @param string|null $no
     * @param string|null $phone
     */
    public function __construct(?int $employee_id = null, ?string $name = null, ?string $surname = null, ?string $job = null, ?int $wage =  null, ?int $room = null, ?string $login = null, ?string $password = null, ?int $admin = null)
    {
        $this->employee_id = $employee_id;
        $this->name = $name;
        $this->surname = $surname;
        $this->job = $job;
        $this->wage = $wage;
        $this ->room = $room;
        $this->login = $login;
        $this->password = $password;
        $this->admin = $admin;
    }

    public static function findByID(int $id) : ?self
    {
        $pdo = PDOProvider::get();
        $stmt = $pdo->prepare("SELECT * FROM `".self::DB_TABLE."` WHERE `employee_id`= :employeeId");
        $stmt->execute(['employeeId' => $id]);

        if ($stmt->rowCount() < 1)
            return null;

        $employee = new self();
        $employee->hydrate($stmt->fetch());
        return $employee;
    }

    /**
     * @return Employee[]
     */
    public static function getAll($sorting = []) : array
    {
        $sortSQL = "";
        if (count($sorting))
        {
            $SQLchunks = [];
            foreach ($sorting as $field => $direction)
                $SQLchunks[] = "`{$field}` {$direction}";

            $sortSQL = " ORDER BY " . implode(', ', $SQLchunks);
        }

        $pdo = PDOProvider::get();
        $stmt = $pdo->prepare("SELECT * FROM `".self::DB_TABLE."`" . $sortSQL);
        $stmt->execute([]);

        $employees = [];
        while ($employeeData = $stmt->fetch())
        {
            $employee = new Employee();
            $employee->hydrate($employeeData);
            $employees[] = $employee;
        }

        return $employees;
    }

    private function hydrate(array|object $data)
    {
        $fields = ['employee_id', 'name', 'surname', 'job', 'wage', 'room', 'login', 'password', 'admin'];
        if (is_array($data))
        {
            foreach ($fields as $field)
            {
                if (array_key_exists($field, $data))
                    $this->{$field} = $data[$field];
            }
        }
        else
        {
            foreach ($fields as $field)
            {
                if (property_exists($data, $field))
                    $this->{$field} = $data->{$field};
            }
        }
    }

    public function insert() : bool
    {
        $query = "INSERT INTO ".self::DB_TABLE." (`name`, `surname`, `room`, `wage`, `job`, `admin`) VALUES (:name, :surname, :room, :wage, :job, :admin)";
        $stmt = PDOProvider::get()->prepare($query);
        $result = $stmt->execute(['name'=>$this->name, 'surname'=>$this->surname, 'room'=>$this->room, 'wage'=>$this->wage, 'job'=>$this->job, 'admin'=>$this->admin]);
        if (!$result)
            return false;

        $this->employee_id = PDOProvider::get()->lastInsertId();
        return true;
    }

    public function update() : bool
    {
        if (!isset($this->employee_id) || !$this->employee_id)
            throw new Exception("Cannot update model without ID");

        $query = "UPDATE ".self::DB_TABLE." SET `name`= :name, `surname`= :surname, `room`= :room, `wage`= :wage, `job`= :job, `admin= :admin` WHERE `employee_id` = :employeeId";
        $stmt = PDOProvider::get()->prepare($query);
        return $stmt->execute(['id'=>$this->employee_id, 'name'=>$this->name, 'surname'=>$this->surname, 'room'=>$this->room, 'wage'=>$this->wage, 'job'=>$this->job, 'admin'=>$this->admin]);
    }

    public function delete() : bool
    {
        return self::deleteByID($this->employee_id);
    }

    public static function deleteByID(int $employeeId) : bool
    {
        $query = "DELETE FROM `".self::DB_TABLE."` WHERE `employee_id` = :employeeId";
        $stmt = PDOProvider::get()->prepare($query);
        $stmt->execute(['employeeId'=>$employeeId]);

        $keyQuery = "DELETE FROM `key` WHERE `employee` = :employeeId";
        $stmt2 = PDOProvider::get()->prepare($keyQuery);
        $stmt2->execute(['employeeId'=>$employeeId]);

        return true;
    }

    public function validate(&$errors = []) : bool
    {
        if (!isset($this->name) || (!$this->name))
            $errors['name'] = 'Jméno nesmí být prázdné';

        if (!isset($this->surname) || (!$this->surname))
            $errors['surname'] = 'Příjmení musí být vyplněno';

        if (!isset($this->job) || (!$this->job))
            $errors['job'] = 'Pozice musí být vyplněna';

        if (!isset($this->wage) || (!$this->wage))
            $errors['wage'] = 'Plat musí být vyplněn';

        if (!isset($this->room) || (!$this->room))
            $errors['room'] = 'Místo neexistuje';

        if (empty($this->login)) {
            $errors['login'] = 'Login musí být vyplněn';
        }
        
        if (empty($this->password)) {
            $errors['password'] = 'Heslo musí být vyplněno';
        }
        
        if (!isset($this->admin)) {
            $errors['admin'] = 'Admin musí být vyplněn';
        }

        return count($errors) === 0;
    }

    public static function readPost() : self
    {
        $employee = new Employee();
        $employee->employee_id = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);
    
        $employee->name = filter_input(INPUT_POST, 'name');
        if ($employee->name)
            $employee->name = trim($employee->name);
    
        $employee->surname = filter_input(INPUT_POST, 'surname');
        if ($employee->surname)
            $employee->surname = trim($employee->surname);
    
        $employee->job = filter_input(INPUT_POST, 'job');
        if ($employee->job)
            $employee->job = trim($employee->job);
    
        $employee->wage = filter_input(INPUT_POST, 'wage', FILTER_VALIDATE_INT);
        
        $employee->room = filter_input(INPUT_POST, 'room');
        if ($employee->room)
            $employee->room = trim($employee->room);
    
        $employee->login = filter_input(INPUT_POST, 'login');
        if ($employee->login)
            $employee->login = trim($employee->login);
    
        $employee->admin = filter_input(INPUT_POST, 'admin', FILTER_VALIDATE_INT);

        return $employee;
    }
}

