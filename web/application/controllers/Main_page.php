<?php

use Model\Boosterpack_model;
use Model\Post_model;
use Model\User_model;
use System\Emerald\Exception\EmeraldModelNoDataException;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 10.11.2018
 * Time: 21:36
 */
class Main_page extends MY_Controller
{

    public function __construct()
    {

        parent::__construct();

        if (is_prod())
        {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function index()
    {
        $user = User_model::get_user();

        App::get_ci()->load->view('main_page', ['user' => User_model::preparation($user, 'default')]);
    }

    public function get_all_posts()
    {
        $posts =  Post_model::preparation_many(Post_model::get_all(), 'default');
        return $this->response_success(['posts' => $posts]);
    }

    public function get_boosterpacks()
    {
        $posts =  Boosterpack_model::preparation_many(Boosterpack_model::get_all(), 'default');
        return $this->response_success(['boosterpacks' => $posts]);
    }

    public function get_post(int $post_id)
    {
        try {
            $post = new Post_model();
            $post->set_id($post_id);
        } catch (ShadowIgniterException|EmeraldModelNoDataException $e) {
            return $this->response_error(sprintf('Post with id = %s not found!', $post_id));
        }

        return $this->response_success(['post' => Post_model::preparation(new Post_model($post_id))]);
    }


    public function comment()
    {
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $body = $this->input->post();

        $commentData = [
            'assign_id' => $body['postId'],
            'text' => $body['commentText'],
            'user_id' => User_model::get_user()->get_id()
        ];

        try {
            $post = new Post_model();
            $post->set_id($body['postId']);
        } catch (ShadowIgniterException|EmeraldModelNoDataException $e) {
            return $this->response_error(sprintf('Post with id = %s not found!', $body['postId']));
        }

        $body['replyId'] = 1;

        if (isset($body['replyId']))
        {
            try {
                $comment = new \Model\Comment_model();
                $comment->set_id($body['replyId']);
            } catch (ShadowIgniterException|EmeraldModelNoDataException $e) {
                return $this->response_error(sprintf('Comment with id = %s not found!', $body['replyId']));
            }

            $commentData = array_merge($commentData, ['reply_id' => $body['replyId']]);
        }

        \Model\Comment_model::create($commentData); // TODO можно обернуть в эксепшен

        return $this->response_success(['status' => 'created']);
    }


    public function login()
    {
        if (!($login = $this->input->post('login')) || !($password = $this->input->post('password')))
        {
            return $this->response_error('Missing or invalid require parameters');
        }

        try {
            \Model\Login_model::login((string)$login, (string)$password);
        } catch (Exception $e) {
            return $this->response_error($e->getMessage());
        }

        return $this->response_success();
    }


    public function logout()
    {
        if ( ! User_model::is_logged())
        {
            $this->go_back();
        }

        \Model\Login_model::logout();

        $this->go_back();
    }

    public function add_money(){
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $sum = (float)App::get_ci()->input->post('sum');

        if ($sum < 0)
        {
            return $this->response_error('Sum should be more then 0');

        }

        try {
            User_model::get_user()->add_money($sum);
        } catch (ShadowIgniterException $e) {
            return $this->response_error('Ups! Something went wrong!');
        }

        return $this->response_success(['status' => 'money added']);
    }

    public function buy_boosterpack()
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        //TODO логика покупки и открытия бустерпака по алгоритмку профитбанк, как описано в ТЗ
    }


    /**
     *
     * @return object|string|void
     */
    public function like_comment(int $comment_id)
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        try {
            $comment = new \Model\Comment_model();
            $comment->set_id($comment_id);
        } catch (ShadowIgniterException|EmeraldModelNoDataException $e) {
            return $this->response_error(sprintf('Comment with id = %s not found!', $comment_id));
        }

        try {
            \Model\Like_model::likeComment(User_model::get_user(), $comment);
        } catch (Exception $e) {
            return $this->response_error($e->getMessage());
        }

        return $this->response_success(['status' => 'success']);
    }

    /**
     * @param int $post_id
     *
     * @return object|string|void
     */
    public function like_post(int $post_id)
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        try {
            $post = new Post_model();
            $post->set_id($post_id);
        } catch (ShadowIgniterException|EmeraldModelNoDataException $e) {
            return $this->response_error(sprintf('Post with id = %s not found!', $post_id));
        }

        try {
            \Model\Like_model::likePost(User_model::get_user(), $post);
        } catch (Exception $e) {
            return $this->response_error($e->getMessage());
        }

        return $this->response_success(['status' => 'success']);
    }


    /**
     * @return object|string|void
     */
    public function get_boosterpack_info(int $bootserpack_info)
    {
        // Check user is authorize
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }


        //TODO получить содержимое бустерпак
    }

    public function go_back()
    {
        $url = App::get_ci()->agent->referer ?? "/";

        header("Location: {$url}");
        exit();
    }


}
