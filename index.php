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
    $config['account_link'] = '/blog/logout/';
    $config['account_text'] = 'Log Out';
    $config['register_button'] = '';
    if($user->isAdmin()){
        $config['new_post_button'] = ' | <a href="/blog/new_post/">Add new post</a>';
    }else{
        $config['new_post_button'] = '';
    }
}else{
    $config['account_link'] = '/blog/login/';
    $config['account_text'] = 'Log In';
    $config['register_button'] = ' | <a href="/blog/register/">Register</a>';
}




$db = new PDO('mysql:host=localhost;dbname=blog','root','');


$app->get('/', function () use($app, $db, $user, $config){
    

        $posts = $db->query('select * from posts');
        
        $output='';
        foreach ($posts as $post){
            
            $output[] = $post;
        }

    return $app['twig']->render('index.twig', array('posts'=>$output, 'config'=>$config));
});

$app->get('/post/{id}', function ($id) use($app, $db, $user, $config){

        $q = $db->prepare('select * from posts where id=:id limit 1');
        
        $q->execute(array('id'=>$id));
        
        $post = $q->fetch();

       return $app['twig']->render('post.twig', array('post'=>$post, 'config'=>$config));
});



$app->get('/login/', function () use($app, $db, $user, $config){
        $errors = '';
       return $app['twig']->render('login.twig', array('errors'=>$errors, 'config'=>$config));
});



$app->post('/login/', function (\Symfony\Component\HttpFoundation\Request $request) use($app, $db, $user, $config){
    
     
        $login = $request->get('login');
        $password = $request->get('password');
        
        $user->login($login, $password);
        $errors = '';
        if($user->isSigned()){
            $errors = 'OK';
            return $app->redirect('/blog/');
        }
        else{
            foreach($user->log->getErrors() as $error){
                
                $errors.= $error.'<br>';   
            }
            echo $errors;    
        }

       return $app['twig']->render('login.twig', array('errors'=>$errors, 'config'=>$config));
});



$app->get('/register/', function () use($app, $db, $user, $config){
    
    
        
        $errors = '';
       return $app['twig']->render('register.twig', array('errors'=>$errors, 'config'=>$config));
});



$app->post('/register/', function (\Symfony\Component\HttpFoundation\Request $request) use($app, $db, $user, $config){
    
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

       return $app['twig']->render('register.twig', array('errors'=>$errors, 'config'=>$config));
});

$app->get('/logout/', function() use($app, $db, $user, $config){
    
    
    $user->logout();
    return $app->redirect('/blog/');
});

$app->run();