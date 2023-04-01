<?php
require_once __DIR__ . "/../bootstrap/bootstrap.php";

class LoginPage extends BasePage
{
    private ?string $login = null;
    private ?string $password = null;
    private array $errors = [];

    public function render(): void
    {
        session_start();

        $this->prepare();

        if (isset($_SESSION['userId'])) {
            header("Location: ./index.php");
        }
        $this->sendHttpHeaders();

        $m = MustacheProvider::get();
        $data = [
            'lang' => AppConfig::get('app.lang'),
            'title' => $this->title,
            'pageHeader' => $this->pageHeader(),
            'pageBody' => $this->pageBody(),
            'pageFooter' => $this->pageFooter()
        ];

        echo $m->render("page", $data);
    }

    protected function prepare(): void
    {
        $this->login = filter_input(INPUT_POST, 'login');
        $this->password = filter_input(INPUT_POST, 'password');

        if ($this->login == null ||  $this->password == null){
            return;
        }
        $stmt = PDOProvider::get()->prepare("SELECT name, surname, employee_id, login, password, admin FROM employee WHERE login = :login");
        $stmt->execute(["login" => $this->login]);
        $user_password = $stmt->fetch();
        var_dump($user_password);
        if ($this->password != $user_password->password){
            $this->errors[] = true;
            $this->pageBody();
            echo "neni loglej";
            return;
        }
        else
        {
            echo "je";
            //session_abort();
            session_start();
            $_SESSION['userId'] = $user_password->employee_id;
            $_SESSION['username'] = $user_password->login;
            $_SESSION['admin'] = $user_password->admin;
            
            var_dump($_SESSION);
        }
    }

    protected function pageBody()
    {
        return MustacheProvider::get()->render('login', ["login" => $this->login, "errors" => $this->errors]);
    }
}

$page = new LoginPage();
$page->render();

?>