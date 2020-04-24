<?php
session_start();
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
#echo ('hi');
//my first MVC application xD

//Do i need to stay at selected page if added new task?

abstract class aV
    {
#    abstract public function ShowPage($pageName,$pageData,$currentPage,$errors);//TODO: Should i transform this to ($pageName, array('data','currentPage','anything')?
    abstract public function ShowPage($pageName,$pageData);
    }
class ModelDBResult
    {
    function __construct($db_result)
        {
        $this->db_result	= $db_result;
        //now numPage should be numeric and more than 0, so...
        }
    public function GetRequestedData($offset, $size)//TODO: would be nice xD if result would be in "TextTask"
        {
        $this->db_result->data_seek($offset);
        $result_array	= array();
        for($i=0;$i<$size;$i++)
            {
            $data		= $this->db_result->fetch_assoc();
            if (empty($data))
                {
                break;
                }
            $result_array[] = $data;//gethering data...
            }
        return $result_array;
        }
    public function GetNumRows()
        {
        return $this->db_result->num_rows;
        }
    private $db_result;
    }
class M
    {
    private $sortOrder;

    public function UpdateTaskText($taskID, $taskText)
        {
        $this->TouchDBConnection();
        $taskID		= (int)$taskID;

        $query	= 'UPDATE task SET taskText = "'.$this->dbc->escape_string(htmlspecialchars($taskText)).'", status = (status | '.Mark::MARK_EDITED_BY_ADMIN.') WHERE id='.$taskID;;
#        echo $query;
        $result	= $this->dbc->query($query);
#        var_dump($result);
        return $result;//TODO: add checks
        }
    public function SetTaskCompleted($id)
        {
        $this->TouchDBConnection();

        $id	= (int)$id;
        $query	= 'UPDATE task SET status = status | '.Mark::MARK_DONE.' WHERE id='.$id;
#        echo $query;
        $result	= $this->dbc->query($query);
#        var_dump($result);
        return $result;//TODO: add checks
        }
    public function Login($userArray)//untill we have only 1 user, here would be this hack!
        {
        $validAdmin	= new User;
        $validAdmin->SetName('admin');
        $validAdmin->SetPassword('123');
        $validAdmin->SetAdmin(true);

        $checkingUser	= new User;
        $checkingUser->SetName($userArray['name']);
        $checkingUser->SetPassword($userArray['pass']);
        $checkingUser->SetAdmin(true);

#        echo '[',$validAdmin->GetPasswordHash() ,'][', $checkingUser->GetPasswordHash(),']';

        if ($validAdmin->GetName() == $checkingUser->getName() && $validAdmin->GetPasswordHash() == $checkingUser->GetPasswordHash())
            {
#            echo 'Da ist gut!';
            return $checkingUser;
            }
#        echo 'fiasko!';
        return NULL;
        }
    public function AddTask($T)
        {
        $this->TouchDBConnection();
#        var_dump($T);
        $query	= 'INSERT INTO task (name,email,taskText) VALUES ("'.$this->dbc->escape_string(htmlspecialchars($T->name)).'","'.$this->dbc->escape_string(htmlspecialchars($T->email)).'","'.$this->dbc->escape_string(htmlspecialchars($T->text)).'")';
#        echo $query;
        $result	= $this->dbc->query($query);
#        var_dump($result);
        return $result;//TODO: add checks
        }

    public function GetTaskText($id)
        {
        $this->TouchDBConnection();
        $id		= (int)$id;
        $query		= 'SELECT taskText FROM task WHERE id='.$id;
        $db_result	= $this->dbc->query($query);
        return new ModelDBResult($db_result);
        }

    public function GetIndexPages($changeSortOrder=0)
        {
#        $changeSortOrder=5;
        //now numPage should be numeric and more than 0, so...
#        $limit = $this->tasksPerPage.' OFFSET '.(($currentPage-1)*$this->tasksPerPage);
        //TODO: add checks if error
        $this->TouchDBConnection();
        $selectFields	= array('id', 'name', 'email', 'taskText', 'status');
        if ($changeSortOrder > 0 && $changeSortOrder <= count($selectFields))
            {
#            echo '[',abs($this->sortOrder),' == ',$changeSortOrder,']';
            if (abs($this->sortOrder) == $changeSortOrder)
                {
                $this->sortOrder *= -1;
                }
            else
                {
                $this->sortOrder = $changeSortOrder;
                }
            }
        $key		= abs($this->sortOrder) - 1;
        $value		= $selectFields[$key];
        $orderBy	= 'ORDER BY '.$value;
        if ($this->sortOrder < 0)
            {
            $orderBy.=' DESC';
            }
        $query		= 'SELECT '.implode(',',$selectFields).' FROM task '.$orderBy;
#        echo $query;
        $db_result	= $this->dbc->query($query);
#        var_dump($db_result);
        return new ModelDBResult($db_result);
/*
#        $db_offset	= (($currentPage-1)*$this->tasksPerPage);
        $db_result->data_seek($db_offset);
        $result_array	= array();
        while(true)
            {
            $data		= $db_result->fetch_assoc();
            if (empty($data))
                {
                break;
                }
            $result_array[] = $data;
            //gethering data...
            }
#        var_dump($db_result);
#        echo '<pre>';var_dump($result_array);
        return $result_array;
*/
        }
    public function GetDataForPage($pageName, $changeSortOrder=0)
        {
#        echo 'here your data!';
        switch($pageName)
            {
            case 'index':
                return $this->GetIndexPages($changeSortOrder);
                break;
            }
        }
    public function TouchDBConnection()//TODO: just re-check, do not connect each time
        {
        require_once('config.php');
        if ($this->dbc && is_resource($this->dbc) && $this->dbc->ping())
            {
            //its ok
            }
        else
            {
            $this->dbc	= new mysqli(DB_HOST, DB_USER, DB_PASS, DB_BASE);
            if ($this->dbc->connect_errno)
                {
                echo 'Connect failed: ', $this->dbc->connect_error;
                //TODO:Panic!
                }
            }
        }
    public function CloseDBConnection()
        {
        if ($this->dbc && is_resource($this->dbc) && $this->dbc->ping())
            {
            $this->dbc->close();
            }
        }
    function __construct()
        {
        //i swear here was a data
        $this->sortOrder=1;
        }
    private $dbc;
    }

class ViewerPageInfo
    {
    public $currentPage=1;
    public $numTasks=0;
    public $offset=0;
    public $numPages=0;

    public $tasksPerPage=3;
    }

class V extends aV
    {
    //public $tasksPerPage = 3;
    private $PageInfo=NULL;
    

    private function TopMenu($pageData)
        {
        echo '<table border="0" width="100%"><tr><td align="right">';
        if ($pageData['Controller']->GetUser()->IsLogged())
            {
            echo '<button onclick="location.href=\'?logout\'">Logout</button>';
            }
        else
            {
            echo '<button onclick="location.href=\'?login\'">Login</button>';
            }
        echo '</td></tr></table>';//like menu xD
        echo '<hr>';
        }

    public function ShowLoginPage($pageData)
        {
        $this->TopMenu($pageData);
        echo '<center>';
        if (!empty($pageData['errors']))
            {
            $this->DisplayError($pageData['errors']);
            }
#        echo 'Login page xD';
        if($pageData['Controller']->GetUser()->IsLogged())
            {
            echo 'Already logged! Go to <a href="index.php">main?</a>';
            }
        else
            {
            
        echo '<form action="?login" method="POST">
name:<br/><input type="text" name="user[name]"/><br/>
pass:<br/><input type="password" name="user[pass]"/><br/>
<input type="submit" value="Login!">
</form>
';
        echo '</center>';
            }
        }


    public function DisplayError($errors)
        {
//        echo 'asdfasdf';
//->errors HACK! Should be done by JQuery
        if (!empty($errors))
            {
            foreach ($errors as $error)
                {
                $subsystem	= '';
                $detailed	= '';
                switch ($error[0])
                    {
                    case C::ERROR_TASK_VALIDATION:
                        $subsystem	= 'проверка задания';
                        switch ($error[1])
                            {
                            case TextTask::ERROR_NAME:
                                $detailed	= 'ошибка в имени';
                                break;
                            case TextTask::ERROR_EMAIL:
                                $detailed	= 'ошибка в email';
                                break;
                            case TextTask::ERROR_TEXT:
                                $detailed	= 'ошибка в тексте задания';
                                break;
                            }
                        break;
                    case C::ERROR_LOGIN_ERROR:
                        $subsystem	= 'ошибка авторизации';
                        $detailed	= 'Проверьте вводимые данные';
                        break;
                    }
                echo '<script>alert("error: ',$subsystem,':',$detailed,'")</script>';
                }
            }
        //TODO
        else
            {
            var_dump($errors);
            }
//<-errors
    }

    public function ShowEditTaskTextPage($pageData)
        {
#        echo 'ShowEditTaskTextPage($pageData);';
        echo '<center>';
#        var_dump($pageData['dataToEdit']);
        if (!empty($pageData['dataToEdit']))
            {
            echo '<form method="POST" action="?editTaskText='.$pageData['dataToEdit']['id'].'">';
                echo '<textarea name="taskText" cols="40" rows="5">',$pageData['dataToEdit']['taskText'],'</textarea><br/>'.PHP_EOL;
                echo '<input type="submit" value="edit Task">'.PHP_EOL;
        
                echo '</form>';
            }
        echo '<a href="index.php">return to main</a>';
        echo '</center>';
        }
#    public function ShowIndexPage($data, $currentPage, $errors)
    public function ShowIndexPage($pageData)
        {
#        echo 'ShowIndexPage(',$currentPage,')<PRE>';
#        var_dump($data);
#        echo 'PageInfo=[',var_dump($this->PageInfo),']';

        //TODO: move to own method
        
        $this->PageInfo->numTasks	= $pageData['data']->GetNumRows();
        $this->PageInfo->numPages	= (int)ceil($this->PageInfo->numTasks / $this->PageInfo->tasksPerPage);
        if ($pageData['currentPage'] > 0 && $pageData['currentPage'] <= $this->PageInfo->numPages )
            {
            $this->PageInfo->currentPage	= $pageData['currentPage'];
#            echo '[CUURENT PAGE CHANGED!]';
            }
        $this->PageInfo->offset		= (($this->PageInfo->currentPage-1)*$this->PageInfo->tasksPerPage);

#        echo 'Numtasks: ', $data->GetNumRows();
#        echo 'offset[',$offset,'] $this->PageInfo->tasksPerPage[',$this->PageInfo->tasksPerPage,']';


        $data	= $pageData['data']->GetRequestedData($this->PageInfo->offset, $this->PageInfo->tasksPerPage);
#        echo '<pre>';var_dump($data);

        echo '<head>',PHP_EOL;
#        echo '<link rel="stylesheet" type="text/css" href="css/bootstrap-sortable.css">',PHP_EOL;
#        echo '<script src="js/jquery-3.5.0.min.js"></script>',PHP_EOL;
#        echo '<script src="js/moment.min.js"></script>',PHP_EOL;
#        echo '<script src="js/bootstrap-sortable.js"></script>',PHP_EOL;
        echo '</head>',PHP_EOL;

        echo '<body>',PHP_EOL;
        $this->TopMenu($pageData);
        echo '<center>',PHP_EOL;//The easiest way xD

#        echo '<table class = "sortable">';
        echo '<table border="1">';
        echo '<thead>';
        echo '<tr>';
        echo '<th><a href="index.php?SortOrderField=2">имя</a></th>';
        echo '<th><a href="index.php?SortOrderField=3">имэйл</a></th>';
        echo '<th>текст</th>';
        echo '<th><a href="index.php?SortOrderField=5">статус</a></th>';
        if ($pageData['Controller']->GetUser()->IsAdmin())
            {
            echo '<th>Set completed</th>';
            echo '<th>EDIT</th>';
            }
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($data as $row)
            {
#            echo '<tr><td>',implode('</td><td>',$row),'</td></tr>';
            echo '<tr>';
            echo '<td>',$row['name'],'</td>';
            echo '<td>',$row['email'],'</td>';
            echo '<td>',$row['taskText'],'</td>';
            $Mark	= new Mark($row['status']);
            echo '<td>',implode($Mark->GetAllTextMarks()),'</td>';
            if ($pageData['Controller']->GetUser()->IsAdmin())
                {
                echo '<td><a href="?setCompleted=',$row['id'],'">завершить</a></td>';
                echo '<td><a href="?editTaskText=',$row['id'],'">редактировать</a></td>';
                }
            echo '</tr>';
            }
        echo '</tbody>';
        echo '</table>';
        for ($i=1;$i<=$this->PageInfo->numPages;$i++)//TODO make current page inactive
            {
            echo '<a href="index.php?currentPage=',$i,'">[Page',$i,']</a>';
            }
        echo '<hr/>';
        if (!empty($pageData['errors']))
            {
            $this->DisplayError($pageData['errors']);
            }
        //we don't need to check "corrections" at client side (js)
        echo '<form action="?requestAddTask" method="POST">'.PHP_EOL;
#        var_dump($pageData['dataToEdit']);
        echo 'name:<br/><input type="text" name="addTask[name]"/><br/>'.PHP_EOL;
        echo 'email:<br/><input type="text" name="addTask[email]"/><br/>'.PHP_EOL;
        echo '<textarea name="addTask[text]" cols="40" rows="5"></textarea><br/>'.PHP_EOL;
        echo '<input type="submit" value="add Task">'.PHP_EOL;
        echo '</form>'.PHP_EOL;;
#        echo '<br>CurrentPage is ',var_dump($this->PageInfo);
        echo '</center>',PHP_EOL;//The easiest way xD
        echo '</body>',PHP_EOL;
        }
    public function ShowPage($pageName, $pageData)
        {
        switch($pageName)
            {
            case 'index':
                $this->ShowIndexPage($pageData);
                break;
            case 'login':
                $this->ShowLoginPage($pageData);
                break;
            case 'editTaskText':
                $this->ShowEditTaskTextPage($pageData);
                break;
            }
        }
    function __construct()
        {
        //i swear here was a data
        $this->PageInfo = new ViewerPageInfo();
        }
    }
class TextTask
    {
    const ERROR_NO_ERROR	= 0;
    const ERROR_NAME		= 1;
    const ERROR_EMAIL		= 2;
    const ERROR_TEXT		= 3;

    public function CheckAndSetName($name)
        {
        //some checks for $name
        if (!empty($name))
            {
            $this->name	= $name;
            return true;
            }
        return false;
        }
    public function CheckAndSetEmail($email)
        {
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL))
            {
            $this->email	= $email;
            return true;
            }
        return false;
        }
    public function CheckAndSetText($text)
        {
        if (!empty($text))
            {
#            $this->text	= htmlspecialchars($text);
            $this->text	= $text;
            return true;
            }
        return false;
        }
    public function IsAllOK()
        {
        return $this->GetLastError() == self::ERROR_NO_ERROR;
        }
    public function GetLastError()
        {
        return $this->error;
        }
    public function __get($prop)
        {
        switch($prop)
            {
            case 'name':
                return $this->name;
            case 'email':
                return $this->email;
            case 'text':
                return $this->text;
            }
        }
    function __construct($name, $email, $text)
        {
        //TODO: now would be detected FIRST error
        if ($this->CheckAndSetName($name) != true)
            {
            $this->error	= self::ERROR_NAME;
            return;
            }
        if ($this->CheckAndSetEmail($email) != true)
            {
            $this->error	= self::ERROR_EMAIL;
            return;
            }
        if ($this->CheckAndSetText($text) != true)
            {
            $this->error	= self::ERROR_TEXT;
            return;
            }
//        $this=NULL;
        }
    private $name		= NULL;
    private $email		= NULL;
    private $text		= NULL;
    private $error		= self::ERROR_NO_ERROR;
    }
class Mark//hmm... Do i really need to strore ALL marks simultaneousely??!
    {
    const MARK_NONE		= 0;

    const MARK_DONE		= 1<<0;
    const MARK_EDITED_BY_ADMIN	= 1<<1;
    public function GetText($markBitPosition)
        {
        switch ($markBitPosition)
            {
#            case self::MARK_NONE:
#                return '';
#                break;
            case self::MARK_DONE:
                return '[выполнено]';
                break;
            case self::MARK_EDITED_BY_ADMIN:
                return '[отредактировано администратором]';
                break;
            }
        }
    public function GetAllTextMarks()//TODO:it can be better
        {
        $result	= array();
        if ($this->IsDone())
            {
            $result[]	= $this->GetText(self::MARK_DONE);
            }
        if ($this->IsEditedByAdmin())
            {
            $result[]	= $this->GetText(self::MARK_EDITED_BY_ADMIN);
            }
        return $result;
        }
    public function IsDone()
        {
        return ($this->mark & (self::MARK_DONE));
        }
    public function IsEditedByAdmin()
        {
        return ($this->mark & (self::MARK_EDITED_BY_ADMIN));
        }
    function __construct($markInt=0)
        {
        $this->mark	= (int)$markInt;
        }
    private $mark;
    //so... mark are (1<<mark) so we can hold up to (32-1) mark inside 32bit int var
    }
class User
    {
    public function Logout()
        {
        $this->isAdmin		= false;
        $this->name		= '';
        $this->passwordHash	= '';
        }
    public function SetName($name)
        {
        $this->name	= $name;
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
        $this->passwordHash	= sha1($this->GetName().$text_pass);
        }
    public function IsLogged()
        {
        if (empty($this->GetName()) || empty($this->getPasswordHash()))
            {
            return false;
            }
        return true;
        }
    public function SetAdmin($true_or_false)
        {
        if ($true_or_false === true)//protection from trash in var
            {
            $this->isAdmin	= true;
            }
        else
            {
            $this->isAdmin	= false;
            }
        }
    private $passwordHash	= '';
    private $name		= '';
    private $isAdmin		= false;
    }

class C
    {
    const ERROR_NO_ERROR	= 0;
    const ERROR_TASK_VALIDATION	= 1;
    const ERROR_PAGE_NOT_FOUND	= 2;
    const ERROR_LOGIN_ERROR	= 3;
    const ERROR_UPDATE_TASK	= 4;

    public function Polling()
        {
//        echo 'let\'s freeze this CPU!';
        $pageName			= 'index';
        $currentPageChangeRequest	= 0;
        $SortOrderField			= 0;
        $errors				= array();
        $data				= '';

        $pageData			= array();
        $pageData['Controller']		= $this;
        $pageData['errors']		= array();

        if (!empty($_GET['currentPage']))//will try to change currentPage
            {
            $currentPageChangeRequest	= (int)$_GET['currentPage'];
            }
        if (isset($_GET['logout']))
            {
            $this->GetUser()->Logout();
            }
        if (isset($_GET['setCompleted']))
            {
#            echo '222';
            $id	= (int)$_GET['setCompleted'];
            if ($this->GetUser()->IsAdmin() && $id > 0)
                {
#                echo '111';
                $this->pM->SetTaskCompleted($id);
                }
            }
        if (isset($_GET['editTaskText']))
            {
            $pageName	= 'editTaskText';
            $taskID	= (int)$_GET['editTaskText'];
            if ($this->GetUser()->IsAdmin() && $taskID > 0)
                {
                if (!empty($_POST['taskText']))
                    {
                    if ($this->pM->UpdateTaskText($taskID, $_POST['taskText']))//do i need some checks?
                        {
                        //ok
                        }
                    else
                        {
                        $pageData['errors'][]	= array(self::ERROR_UPDATE_TASK, 0);
                        }
                    }
                else
                    {
                    $requestedData		= $this->pM->GetTaskText($taskID)->GetRequestedData(0,1);
                    $taskText		= $requestedData[0]['taskText'];//TODO: a little hack with [0]
#                    var_dump($requestedData);
#                    $requestedData		= 
                    $pageData['dataToEdit']	= array('taskText'=>$taskText,'id'=>$taskID);
#                    var_dump($pageData['dataToEdit']);
                    }
                }
            }
        if (isset($_GET['login']))
            {
            $pageName	= 'login';
            if (!empty($_POST['user']))
                {
                $result	= $this->pM->Login($_POST['user']);
#                var_dump($result);
                if ($result)
                    {
#                    echo 'authentificating..';
                    $this->user	= &$result;
                    }
                else
                    {
                    //wrong
                    $pageData['errors'][]	= array(self::ERROR_LOGIN_ERROR,0);
                    }
                }
            }

        if (isset($_GET['requestAddTask']))
            {
            $data	= &$_POST['addTask'];
            if (!empty($data))
                {
#                echo '<PRE>';var_dump($data);echo '</PRE>';
                $T	= new TextTask($data['name'],$data['email'],$data['text']);
                if ($T->IsAllOK())
                    {
#                echo '<PRE>';var_dump($T);echo '</PRE>';
                    $this->pM->AddTask($T);
                    }
                else
                    {
                    $pageData['errors'][]	= array(self::ERROR_TASK_VALIDATION,$T->GetLastError());
                    }
                }
            }
        if (!empty($_GET['SortOrderField']))
            {
            /*
            sort mechanism:
            valid num 1,2,3...n 0 - invalid
            1 mean sort by "0" field, "name" OSC
            -1 mean sort by "0" field, "name" DESC
            */
            $SortOrderField	= (int)$_GET['SortOrderField'];
            }
#        $currentPage	= (int)$_GET['numPage'];//there would be error if $_GET var does now exists. Error supressed by error level by default
#        $currentPage=2;
#        echo '[',$currentPageChangeRequest,']';

//	to $tnis->ShowPage/ProcessPage/Something ?
#echo '[',$pageName,']';var_dump($this->knownPages);
        if (isset($this->knownPages[$pageName]))
            {
            switch($pageName)
                {
                case 'index'://TODO: migrate to new format
                    $pageData['data']		= &$data;
                    $pageData['currentPage']	= $currentPageChangeRequest;

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
        die('errno:'.$errno);
        }
    function __construct()
        {
        $this->knownPages=array('index'=>1,'login'=>1, 'logout'=>1, 'editTaskText'=>1);
        $this->pM	= new M;
        $this->pV	= new V;
        $this->user	= new User;
        }
    private $knownPages;
    private $pM;
    private $pV;
    private $user;
    }

if (!isset($_SESSION['C']))
    {
    $_SESSION['C']	= new C;
#    echo 'NEW called!';
    }
$C	= &$_SESSION['C'];
#$t	= new TextTask('n','e','t');
#echo $t->GetLastError();
#var_dump($t);
//is there can be that $_SESSION['C'] would be empty?
//TODO: add checks that there is our class, but not now
$C->Polling();

#echo '<hr/><PRE>';@var_dump($C);
$C->EndOfPage();

?>