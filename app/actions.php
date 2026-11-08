<?php
if ($_SERVER['REQUEST_METHOD']==='POST') {
        try {
                if (empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0)>0){
                        throw new RuntimeException('Upload exceeds the server request limit. Use photos under 2 MB each and set post_max_size to 16M in php.ini.');
                }

                if (!hash_equals($_SESSION['csrf'],post('csrf'))) {
                        http_response_code(403);
                        throw new RuntimeException('Session expired. Reload this page and try again.'); 
                }

                $action=post('action');

                if($action==='login') {
                        $s=db()->prepare('SELECT * FROM php_users WHERE LOWER(username)=LOWER(?)');
                        $s->execute([post('username')]);
                        $row=$s->fetch();

                        if(!$row || !password_verify($_POST['password']??'', $row['password_hash'])) {
                                throw new RuntimeException('Invalid username or password.');
                        }

                        $u=json_decode($row['body']);

                        if(!$u->isActive){
                                throw new RuntimeException('Account is inactive.');
                        }

                        session_regenerate_id(true);
                        $_SESSION['user_id']=$u->id;
                        $_SESSION['csrf']=bin2hex(random_bytes(32));
                        $u->lastLogin=date(DATE_ATOM);
                        saveUser($u);
                        go('dashboard');
                }

                if($action==='register') {
                        if(!post('firstName') || !post('lastName')){
                                throw new RuntimeException('First and last name are required.');
                        }

                        if(($_POST['password']??'')!==($_POST['confirmPassword']??'')){
                                throw new RuntimeException('Passwords do not match.');
                        }

                        $u=obj([
                                'id'=>bin2hex(random_bytes(12)),
                                'username'=>post('username'),
                                'email'=>post('email'),
                                'role'=>'user',
                                'profile'=>array_fill_keys(['firstName','lastName','phone','studentId','department','position'],''),
                                'createdAt'=>date(DATE_ATOM),
                                'lastLogin'=>null,
                                'isActive'=>true]);

                        foreach($u->profile as $k=>$v){
                                $u->profile->$k=post($k);
                        }

                        saveUser($u,$_POST['password']??'');
                        $_SESSION['flash']='Account created. Please log in.';
                        go('login');
                }
        } catch (RuntimeException $ex) { 
                $errorMessage=$ex->getMessage();
        }
}
?>