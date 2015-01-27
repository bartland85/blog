<?php

require_once 'vendor/autoload.php';

$app = new Silex\Application();
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    
    'twig.path' => __DIR__.'/views'
));
$app['debug'] = true;


$user = new ptejada\uFlex\User();
$user->config->database->update(array(
            'host'=>'localhost',
            'name'=>'blog',
            'user'=>'root',
            'Password'=>''
            
        ));
$user->start();

if($user->isSigned()){
    $account_link = '/blog/logout/';
    $account_text = 'Log Out';
}else{
    $account_link = '/blog/login/';
    $account_text = 'Log In';
}

$db = new PDO('mysql:host=localhost;dbname=blog','root','');


$app->get('/', function () use($app, $db, $user){
    
        
        $posts = $db->query('select * from posts');
        
        $output='';
        foreach ($posts as $post){
            
            $output[] = $post;
        }
           
    return $app['twig']->render('posts.twig', array('posts'=>$output, 'account_link'=>$account_link, 'account_text'=>$account_text));
});

$app->get('/post/{id}', function ($id) use($app, $db, $user){

        $q = $db->prepare('select * from posts where id=:id limit 1');
        
        $q->execute(array('id'=>$id));
        
        $post = $q->fetch();

       return $app['twig']->render('post.twig', array('post'=>$post));
});



$app->get('/login/', function () use($app, $db, $user){
            
        
        $errors = '';
       return $app['twig']->render('login.twig', array('errors'=>$errors));
});



$app->post('/login/', function (\Symfony\Component\HttpFoundation\Request $request) use($app, $db, $user){
    
     
        $login = $request->get('login');
        $password = $request->get('password');
        
        $user->login($login, $password);
        $errors = '';
        if($user->isSigned()){
            $errors = 'OK';
        }
        else{
            foreach($user->log->getErrors() as $error){
                
                $errors.= $error.'<br>';   
            }
            echo $errors;    
        }

       return $app['twig']->render('login.twig', array('errors'=>$errors));
});



$app->get('/register/', function () use($app, $db, $user){
    
    
        
        $errors = '';
       return $app['twig']->render('register.twig', array('errors'=>$errors));
});



$app->post('/register/', function (\Symfony\Component\HttpFoundation\Request $request) use($app, $db, $user){
    
        $data = new ptejada\uFlex\Collection($_POST);
        
        
        $registered = $user->register(array(
            
            'Username'=>$data->login,
            'Password'=>$data->password,
            'Password2'=>$data->password2,
            'Email'=>$data->email
            
        ));
        
  
        $errors = '';
        if($registered){
            $errors = 'OK';
        }
        else{
            foreach($user->log->getErrors() as $error){
                
                $errors.= $error.'<br>'; 
            }
            echo $errors;    
        }

       return $app['twig']->render('register.twig', array('errors'=>$errors));
});

$app->get('/logout/', function() use($app, $db, $user){
    
    
    $user->logout();
    return $app->redirect('/blog/');
});

$app->run();