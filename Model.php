<?php

class Mark//hmm... Do i really need to strore ALL marks simultaneousely??!
{
    const MARK_NONE = 0;

    const MARK_DONE = 1 << 0;
    const MARK_EDITED_BY_ADMIN = 1 << 1;

    public function GetText($markBitPosition)//TODO: remove string from here
    {
        switch ($markBitPosition) {
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
        $result = array();
        if ($this->IsDone()) {
            $result[] = $this->GetText(self::MARK_DONE);
        }
        if ($this->IsEditedByAdmin()) {
            $result[] = $this->GetText(self::MARK_EDITED_BY_ADMIN);
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

    function __construct($markInt = 0)
    {
        $this->mark = (int)$markInt;
    }

    private $mark;
    //so... mark are (1<<mark) so we can hold up to (32-1) mark inside 32bit int var
}

class TextTask
{
    const ERROR_NO_ERROR = 0;
    const ERROR_NAME = 1;
    const ERROR_EMAIL = 2;
    const ERROR_TEXT = 3;

    public function CheckAndSetName($name)
    {
        //some checks for $name
        if (!empty($name)) {
            $this->name = $name;
            return true;
        }
        return false;
    }

    public function CheckAndSetEmail($email)
    {
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->email = $email;
            return true;
        }
        return false;
    }

    public function CheckAndSetText($text)
    {
        if (!empty($text)) {
#            $this->text	= htmlspecialchars($text);
            $this->text = $text;
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
        switch ($prop) {
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
        if ($this->CheckAndSetName($name) != true) {
            $this->error = self::ERROR_NAME;
            return;
        }
        if ($this->CheckAndSetEmail($email) != true) {
            $this->error = self::ERROR_EMAIL;
            return;
        }
        if ($this->CheckAndSetText($text) != true) {
            $this->error = self::ERROR_TEXT;
            return;
        }
//        $this=NULL;
    }

    private $name = NULL;
    private $email = NULL;
    private $text = NULL;
    private $error = self::ERROR_NO_ERROR;
}

class ModelDBResult
{
    function __construct($db_result)
    {
        $this->db_result = $db_result;
    }

    public function GetRequestedData($offset, $size)//TODO: would be nice xD if result would be in "TextTask"
    {
        $this->db_result->data_seek($offset);
        $result_array = array();
        for ($i = 0; $i < $size; $i++) {
            $data = $this->db_result->fetch_assoc();
            if (empty($data)) {
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
        $taskID = (int)$taskID;

        $query = 'UPDATE task SET taskText = "' . $this->dbc->escape_string(htmlspecialchars($taskText)) . '", status = (status | ' . Mark::MARK_EDITED_BY_ADMIN . ') WHERE id=' . $taskID;;
#        echo $query;
        $result = $this->dbc->query($query);
#        var_dump($result);
        return $result;//TODO: add checks
    }

    public function SetTaskCompleted($id)
    {
        $this->TouchDBConnection();

        $id = (int)$id;
        $query = 'UPDATE task SET status = status | ' . Mark::MARK_DONE . ' WHERE id=' . $id;
#        echo $query;
        $result = $this->dbc->query($query);
#        var_dump($result);
        return $result;//TODO: add checks
    }

    public function Login($userArray)//untill we have only 1 user, here would be this hack!
    {
        $validAdmin = new User;
        $validAdmin->SetName('admin');
        $validAdmin->SetPassword('123');
        $validAdmin->SetAdmin(true);

        $checkingUser = new User;
        $checkingUser->SetName($userArray['name']);
        $checkingUser->SetPassword($userArray['pass']);
        $checkingUser->SetAdmin(true);

#        echo '[',$validAdmin->GetPasswordHash() ,'][', $checkingUser->GetPasswordHash(),']';

        if ($validAdmin->GetName() == $checkingUser->getName() && $validAdmin->GetPasswordHash() == $checkingUser->GetPasswordHash()) {
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
        $query = 'INSERT INTO task (name,email,taskText) VALUES ("' . $this->dbc->escape_string(htmlspecialchars($T->name)) . '","' . $this->dbc->escape_string(htmlspecialchars($T->email)) . '","' . $this->dbc->escape_string(htmlspecialchars($T->text)) . '")';
#        echo $query;
        $result = $this->dbc->query($query);
#        var_dump($result);
        return $result;//TODO: add checks
    }

    public function GetTaskText($id)
    {
        $this->TouchDBConnection();
        $id = (int)$id;
        $query = 'SELECT taskText FROM task WHERE id=' . $id;
        $db_result = $this->dbc->query($query);
        return new ModelDBResult($db_result);
    }

    public function GetIndexPages($changeSortOrder = 0)
    {
#        $changeSortOrder=5;
        //now numPage should be numeric and more than 0, so...
#        $limit = $this->tasksPerPage.' OFFSET '.(($currentPage-1)*$this->tasksPerPage);
        //TODO: add checks if error
        $this->TouchDBConnection();
        $selectFields = array('id', 'name', 'email', 'taskText', 'status');
        if ($changeSortOrder > 0 && $changeSortOrder <= count($selectFields)) {
#            echo '[',$this->sortOrder,'abs:',abs($this->sortOrder),' == ',$changeSortOrder,']';
            if (abs($this->sortOrder) == $changeSortOrder) {
                $this->sortOrder *= -1;
            } else {
#                echo 'Setting $this->sortOrder to ', $changeSortOrder,'!';
                $this->sortOrder = $changeSortOrder;
            }
        }
//        echo '[',$this->sortOrder,'abs:',abs($this->sortOrder),' == ',$changeSortOrder,']';
        $key = abs($this->sortOrder) - 1;
        $value = $selectFields[$key];
        $orderBy = 'ORDER BY ' . $value;
        if ($this->sortOrder < 0) {
            $orderBy .= ' DESC';
        }
        $query = 'SELECT ' . implode(',', $selectFields) . ' FROM task ' . $orderBy;
#        echo $query;
        $db_result = $this->dbc->query($query);
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

    public function GetDataForPage($pageName, $changeSortOrder = 0)
    {
#        echo 'here your data!';
        switch ($pageName) {
            case 'index':
                return $this->GetIndexPages($changeSortOrder);
                break;
        }
    }

    public function TouchDBConnection()//TODO: just re-check, do not connect each time
    {
        require_once('config.php');
        if ($this->dbc && is_resource($this->dbc) && $this->dbc->ping()) {
            //its ok
        } else {
            $this->dbc = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_BASE);
            if ($this->dbc->connect_errno) {
                echo 'Connect failed: ', $this->dbc->connect_error;
                //TODO:Panic!
            }
        }
    }

    public function CloseDBConnection()
    {
        if ($this->dbc && is_resource($this->dbc) && $this->dbc->ping()) {
            $this->dbc->close();
        }
    }

    function __construct()
    {
        //i swear here was a data
        $this->sortOrder = 1;
    }

    private $dbc;
}
