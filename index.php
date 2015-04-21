 <?php

 require_once 'lib/Config/define.php';
require_once 'vendor/autoload.php';


$app = new Silex\Application();
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    
    'twig.path' => __DIR__.'/views'
));
$app['debug'] = true;

 use Silex\Provider\FormServiceProvider;

 $app->register(new FormServiceProvider());


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
    $config['user_id'] = $user->__get('ID');

    if($user->isAdmin()){
        $config['new_post_button'] = ' | <a href="/blog/edit/">Add new post</a>';
    }else{
        $config['new_post_button'] = '';
    }
}else{
    $config['account_link'] = '/blog/login/';
    $config['account_text'] = 'Log In';
    $config['register_button'] = ' | <a href="/blog/register/">Register</a>';

    $config['new_post_button'] = '';
}




$db = new PDO('mysql:host=localhost;dbname=blog','root','');


$app->get('/', function () use($app, $db, $user, $config){

    $postList = new \Bart\PostList();

    $posts = $postList->getNewest2Limit();

    foreach($posts as $k=>$post){
        if($post->getUserId() == $user->getProperty('ID')){
            $post->setEditable(true);
        }
    }

    return $app['twig']->render('index.twig', array('posts'=>$posts, 'config'=>$config));
});

$app->get('/post/{id}', function ($id) use($app, $db, $user, $config){

        $post = new \Bart\Post();

        $post->getById($id);

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

 $app->match('/edit/{id}', function(\Symfony\Component\HttpFoundation\Request $request, $id)use($app, $db, $user, $config){

     $post = new \Bart\Post();
    if($id > 0){

        $post->getById($id);
    }

     $form = $app['form.factory']->createBuilder('form')
         ->add('title', 'text', array('label'=>'Post\'s title', 'required'=>true, 'data'=>$post->getTitle()))
         ->add('text', 'textarea', array('label'=>'Post\'s text',  'required'=>false, 'data'=>$post->getText()))
         ->add('id', 'hidden', array('required'=>false, 'data'=>$post->getId()))
         ->getForm();

     $form->handleRequest($request);


     if($form->isValid()){

         $params = array(
             ':title'=>$request->request->get('form')['title'],
             ':text'=>$request->request->get('form')['text'],
             ':user_id'=>$config['user_id']
         );

        if($request->get('id') > 0){

            $insert = $db->prepare('update posts set title=:title, text=:text, user_id=:user_id where id=:id');

            $params[':id'] = $id;

            $operation = 'update';

        }else {
            $insert = $db->prepare('insert into posts(title, text, user_id, datetime)values(:title, :text, :user_id, now())');
            $operation = 'insert';
        }
         $insert->execute($params);

         if($operation == 'insert')
            return $app->redirect('/blog/edit/' . $db->lastInsertId());
     }

        return $app['twig']->render('edit.twig', array('config'=>$config, 'form'=>$form->createView()));
 })->value('id', '-1');

$app->run();