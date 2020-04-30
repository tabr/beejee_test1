<?php

abstract class aV
{
    abstract public function ShowPage($pageName, $pageData);
}

class ViewerPageInfo
{
    public $currentPage = 1;
    public $numTasks = 0;
    public $offset = 0;
    public $numPages = 0;

    public $tasksPerPage = 3;
}

class V extends aV //Or u need a templates?
{
    //public $tasksPerPage = 3;
    private $PageInfo = NULL;


    public function Head($title='')
    {
        echo '<head>', PHP_EOL;
        echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">', PHP_EOL;
        echo '<meta charset="utf-8">',PHP_EOL;
        echo '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">',PHP_EOL;
        echo '<style>
main > .container {
  padding: 60px 15px 0;
}
</style>
        ';
        if (!empty($title)){
            echo '<title>',$title,'</title>';
        }
        echo '</head>', PHP_EOL;
    }

    private function TopMenu($pageData)
    {
        echo '<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">';
        echo '<div class="collapse navbar-collapse">';
        echo '<ul class="navbar-nav mr-auto">';
        echo '<li><a href="index.php"><button onclick="location.href=\'index.php\'">HOME</button></a></li>';
        echo '</ul>';
        if ($pageData['Controller']->GetUser()->IsLogged()) {
                echo '<button onclick="location.href=\'?logout\'">Logout</button>';
                } else {
                echo '<button onclick="location.href=\'?login\'">Login</button>';
                }

        echo '</div>';
        echo '</nav>';
        echo '</header>';

    }

    public function ShowLoginPage($pageData)
    {
        $this->Head('Login');
        echo '<body>';
        $this->TopMenu($pageData);
        echo '<main>';
        echo '<div class="container">';
        echo '<div class="container text-center">';
        echo '<div class="row">';
        echo '<div class="col-sm-12">';
        if (!empty($pageData['errors'])) {
            $this->DisplayError($pageData['errors']);
        }
#        echo 'Login page xD';
        if ($pageData['Controller']->GetUser()->IsLogged()) {
            echo 'Already logged! Go to <a href="index.php">main?</a>';
        } else {

            echo '<form action="?login" method="POST">
name:<br/><input type="text" name="user[name]"/><br/>
pass:<br/><input type="password" name="user[pass]"/><br/>
<input type="submit" value="Login!">
</form>
';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</main>';
            $this->BottomScripts();
            echo '</body>';
        }
    }


    public function DisplayError($errors)
    {
//        echo 'asdfasdf';
//->errors HACK! Should be done by JQuery
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $subsystem = '';
                $detailed = '';
                switch ($error[0]) {
                    case C::ERROR_TASK_VALIDATION:
                        $subsystem = 'проверка задания';
                        switch ($error[1]) {
                            case TextTask::ERROR_NAME:
                                $detailed = 'ошибка в имени';
                                break;
                            case TextTask::ERROR_EMAIL:
                                $detailed = 'ошибка в email';
                                break;
                            case TextTask::ERROR_TEXT:
                                $detailed = 'ошибка в тексте задания';
                                break;
                        }
                        break;
                    case C::ERROR_LOGIN_ERROR:
                        $subsystem = 'ошибка авторизации';
                        $detailed = 'Проверьте вводимые данные';
                        break;
                }
                echo '<script>alert("error: ', $subsystem, ':', $detailed, '")</script>';
            }
        } //TODO
        else {
            var_dump($errors);
        }
//<-errors
    }

    public function BottomScripts()
    {
        echo '<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>',PHP_EOL;
        echo '<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>',PHP_EOL;
        echo '<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>',PHP_EOL;
    }


    public function ShowEditTaskTextPage($pageData)//TODO: hmm...
    {
        $this->Head('Edit');
        echo '<body>';
        $this->TopMenu($pageData);
        echo '<main>';
        echo '<div class="container text-center">';
        echo '<div class="row">';
        echo '<div class="col-sm-12">';
#        var_dump($pageData['dataToEdit']);
        if (!empty($pageData['dataToEdit'])) {
            echo '<form method="POST" action="?editTaskText=' . $pageData['dataToEdit']['id'] . '">';
            echo '<textarea name="taskText" cols="40" rows="5">', $pageData['dataToEdit']['taskText'], '</textarea><br/>' . PHP_EOL;
            echo '<input type="submit" value="edit Task">' . PHP_EOL;

            echo '</form>';
        }
        if (empty($pageData['errors'])){
            echo 'OK';
        }
        else{
            $this->DisplayError($pageData['errors']);
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</main>';
        $this->BottomScripts();
        echo '</body>';
    }

    public function ShowIndexPage($pageData)
    {
        $this->Head();
#        echo 'ShowIndexPage(',$currentPage,')<PRE>';
#        var_dump($data);
#        echo 'PageInfo=[',var_dump($this->PageInfo),']';

        //TODO: move to own method?

        $this->PageInfo->numTasks = $pageData['data']->GetNumRows();
        $this->PageInfo->numPages = (int)ceil($this->PageInfo->numTasks / $this->PageInfo->tasksPerPage);
        if ($pageData['currentPage'] > 0 && $pageData['currentPage'] <= $this->PageInfo->numPages) {
            $this->PageInfo->currentPage = $pageData['currentPage'];
#            echo '[CURRENT PAGE CHANGED!]';
        }
        $this->PageInfo->offset = (($this->PageInfo->currentPage - 1) * $this->PageInfo->tasksPerPage);

#        echo 'Numtasks: ', $data->GetNumRows();
#        echo 'offset[',$offset,'] $this->PageInfo->tasksPerPage[',$this->PageInfo->tasksPerPage,']';


        $data = $pageData['data']->GetRequestedData($this->PageInfo->offset, $this->PageInfo->tasksPerPage);
#        echo '<pre>';var_dump($data);
        $textRowWidth   = 8;
        if ($pageData['Controller']->GetUser()->IsAdmin()) {//TODO: Code duplication
            $textRowWidth-=2;
        }


        echo '<body>', PHP_EOL;
        $this->TopMenu($pageData);
#        echo '<center>', PHP_EOL;//The easiest way xD

#        echo '<table class = "sortable">';
        echo '<main role="main">';
        echo '<div class="container">';//container-fluid
        echo '<div class="container text-center">';
        echo '<div class="row">';

        echo '<div class="col-sm-1"><a href="index.php?SortOrderField=2">имя</a></div>';
        echo '<div class="col-sm-1"><a href="index.php?SortOrderField=3">имэйл</a></div>';
        echo '<div class="col-sm-',$textRowWidth,'">текст</div>';
        echo '<div class="col-sm-2"><a href="index.php?SortOrderField=5">статус</a></div>';

        if ($pageData['Controller']->GetUser()->IsAdmin()) {//TODO: Code duplication
            echo '<div class="col-sm-1">Set completed</div>';
            echo '<div class="col-sm-1">EDIT</div>';
        }
        echo '</div>';
        echo '</div>';

        $i=0;
        foreach ($data as $row) {
            $Mark = new Mark($row['status']);
#            echo '<tr><td>',implode('</td><td>',$row),'</td></tr>';
            echo '<div class="container text-center">';
            if ((++$i) % 2 == 1 )
                echo '<div class="row bg-info">';
            else
                echo '<div class="row bg-light">';


            echo '<div class="col-sm-1">', $row['name'], '</div>';
            echo '<div class="col-sm-1">', $row['email'], '</div>';
            echo '<div class="col-sm-',$textRowWidth,'">', $row['taskText'], '</div>';
            echo '<div class="col-sm-2">', implode($Mark->GetAllTextMarks()), '</div>';
            if ($pageData['Controller']->GetUser()->IsAdmin()) {//TODO: Code duplication
                echo '<div class="col-sm-1"><a href="?setCompleted=', $row['id'], '">завершить</a></div>';
                echo '<div class="col-sm-1"><a href="?editTaskText=', $row['id'], '">редактировать</a></div>';
            }
            echo '</div>';
            echo '</div>';
        }
        echo '<hr/>';
        echo '<div class="container text-center">';
        echo '<div class="row">';
        echo '<div class="col-sm-12">';
        echo '<nav aria-label="Page navigation">';
        echo '<ul class="pagination justify-content-center">';
        for ($i = 1; $i <= $this->PageInfo->numPages; $i++)//TODO: make current page inactive?; if pages more than screen?
        {
            echo '<li class="page-item"><a class="page-link" href="index.php?currentPage=', $i, '">Page', $i, '</a></li>';
        }
        echo '</ul">';
        echo '</nav>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '<hr/>';
        echo '<div class="container text-center">';
        echo '<div class="row">';
        echo '<div class="col-sm-12">';
        //we don't need to check "corrections" at client side (js)
        echo '<form action="?requestAddTask" method="POST">' . PHP_EOL;
#        var_dump($pageData['dataToEdit']);
        echo 'name:<br/><input type="text" name="addTask[name]"/><br/>' . PHP_EOL;
        echo 'email:<br/><input type="text" name="addTask[email]"/><br/>' . PHP_EOL;
        echo '<textarea name="addTask[text]" cols="40" rows="5"></textarea><br/>' . PHP_EOL;
        echo '<input type="submit" value="add Task">' . PHP_EOL;
        echo '</form>' . PHP_EOL;;
        echo '</div>';
        echo '</div>';
        echo '</div>';

        echo '</div>';//container-fluid
        echo '</main>';
#        echo '<br>CurrentPage is ',var_dump($this->PageInfo);
#        echo '</center>', PHP_EOL;//The easiest way xD


        if (!empty($pageData['errors'])) {
            $this->DisplayError($pageData['errors']);
        }
        $this->BottomScripts();
        echo '</body>', PHP_EOL;
    }

    public function ShowPage($pageName, $pageData)
    {
        switch ($pageName) {
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
