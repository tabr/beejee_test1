<?php

class C
{
    const ERROR_NO_ERROR = 0;
    const ERROR_TASK_VALIDATION = 1;
    const ERROR_PAGE_NOT_FOUND = 2;
    const ERROR_LOGIN_ERROR = 3;
    const ERROR_UPDATE_TASK = 4;

    public function Polling()
    {
//        echo 'let\'s freeze this CPU!';
        $pageName = 'index';
        $currentPageChangeRequest = 0;
        $SortOrderField = 0;
        $errors = array();
        $data = '';

        $pageData = array();
        $pageData['Controller'] = $this;
        $pageData['errors'] = array();

        if (!empty($_GET['currentPage']))//will try to change currentPage
        {
            $currentPageChangeRequest = (int)$_GET['currentPage'];
        }
        if (isset($_GET['logout'])) {
            $this->GetUser()->Logout();
        }
        if (isset($_GET['setCompleted'])) {
#            echo '222';
            $id = (int)$_GET['setCompleted'];
            if ($this->GetUser()->IsAdmin() && $id > 0) {
#                echo '111';
                $this->pM->SetTaskCompleted($id);
            }
        }
        if (isset($_GET['editTaskText'])) {
            $pageName = 'editTaskText';
            $taskID = (int)$_GET['editTaskText'];
            if ($this->GetUser()->IsAdmin() && $taskID > 0) {
                if (!empty($_POST['taskText'])) {
                    if ($this->pM->UpdateTaskText($taskID, $_POST['taskText']))//do i need some checks?
                    {
                        //ok
                    } else {
                        $pageData['errors'][] = array(self::ERROR_UPDATE_TASK, 0);
                    }
                } else {
                    $requestedData = $this->pM->GetTaskText($taskID)->GetRequestedData(0, 1);
                    $taskText = $requestedData[0]['taskText'];//TODO: a little hack with [0]
#                    var_dump($requestedData);
#                    $requestedData		=
                    $pageData['dataToEdit'] = array('taskText' => $taskText, 'id' => $taskID);
#                    var_dump($pageData['dataToEdit']);
                }
            }
        }
        if (isset($_GET['login'])) {
            $pageName = 'login';
            if (!empty($_POST['user'])) {
                $result = $this->pM->Login($_POST['user']);
#                var_dump($result);
                if ($result) {
#                    echo 'authentificating..';
                    $this->user = &$result;
                } else {
                    //wrong
                    $pageData['errors'][] = array(self::ERROR_LOGIN_ERROR, 0);
                }
            }
        }

        if (isset($_GET['requestAddTask'])) {
            $data = &$_POST['addTask'];
            if (!empty($data)) {
#                echo '<PRE>';var_dump($data);echo '</PRE>';
                $T = new TextTask($data['name'], $data['email'], $data['text']);
                if ($T->IsAllOK()) {
#                echo '<PRE>';var_dump($T);echo '</PRE>';
                    $this->pM->AddTask($T);
                } else {
                    $pageData['errors'][] = array(self::ERROR_TASK_VALIDATION, $T->GetLastError());
                }
            }
        }
        if (!empty($_GET['SortOrderField'])) {
            /*
            sort mechanism:
            valid num 1,2,3...n 0 - invalid
            1 mean sort by "0" field, "name" OSC
            -1 mean sort by "0" field, "name" DESC
            */
            $SortOrderField = (int)$_GET['SortOrderField'];
        }
#        $currentPage	= (int)$_GET['numPage'];//there would be error if $_GET var does now exists. Error supressed by error level by default
#        $currentPage=2;
#        echo '[',$currentPageChangeRequest,']';

//	to $tnis->ShowPage/ProcessPage/Something ?
#echo '[',$pageName,']';var_dump($this->knownPages);
        if (isset($this->knownPages[$pageName])) {
            switch ($pageName) {
                case 'index'://TODO: migrate to new format
                    $pageData['data'] = &$data;
                    $pageData['currentPage'] = $currentPageChangeRequest;

                    $data = $this->pM->GetDataForPage($pageName, $SortOrderField);//TODO: add checks if error or empty data
                    $this->pV->ShowPage($pageName, $pageData);
                    return;
                    break;//this will never happened! I know this!
                case 'login':
                case 'editTaskText':
                    $this->pV->ShowPage($pageName, $pageData);
                    return;
                    break;//this will never happened! I know this!

            }
        }
#        else
#            {
#echo '[',$pageName,']';
        $this->DisplayError(self::ERROR_PAGE_NOT_FOUND);
#            }
    }

    public function GetUser()
    {
        return $this->user;
    }

    public function EndOfPage()
    {
        $this->pM->CloseDBConnection();
    }

    private function DisplayError($errno)//TODO: too many errors in many classes
    {
        die('errno:' . $errno);
    }

    function __construct()
    {
        $this->knownPages = array('index' => 1, 'login' => 1, 'logout' => 1, 'editTaskText' => 1);
        $this->pM = new M;
        $this->pV = new V;
        $this->user = new User;
    }

    private $knownPages;
    private $pM;
    private $pV;
    private $user;
}

class User
{
    public function Logout()
    {
        $this->isAdmin = false;
        $this->name = '';
        $this->passwordHash = '';
    }

    public function SetName($name)
    {
        $this->name = $name;
    }

    public function GetName()
    {
        return $this->name;
    }

    public function IsAdmin()
    {
        return $this->isAdmin;
    }

    public function GetPasswordHash()
    {
        return $this->passwordHash;
    }

    public function SetPassword($text_pass)//TODO: USERNAME should be set
    {
        $this->passwordHash = sha1($this->GetName() . $text_pass);
    }

    public function IsLogged()
    {
        if (empty($this->GetName()) || empty($this->getPasswordHash())) {
            return false;
        }
        return true;
    }

    public function SetAdmin($true_or_false)
    {
        if ($true_or_false === true)//protection from trash in var
        {
            $this->isAdmin = true;
        } else {
            $this->isAdmin = false;
        }
    }

    private $passwordHash = '';
    private $name = '';
    private $isAdmin = false;
}
