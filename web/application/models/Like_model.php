<?php


namespace Model;


use System\Core\CI_Model;

class Like_model extends CI_Model
{
    /**
     * @param User_model $user_model
     * @param Post_model $post_model
     * @throws \Exception
     */
    public static function likePost(User_model $user_model, Post_model $post_model)
    {
        self::likeModel($user_model, $post_model);
    }

    /**
     * @param User_model $user_model
     * @param Comment_model $comment_model
     * @throws \Exception
     */
    public static function likeComment(User_model $user_model, Comment_model $comment_model)
    {
        self::likeModel($user_model, $comment_model);
    }

    /**
     * @param User_model $user_model
     * @param Comment_model|Post_model $comment_model
     * @throws \Exception
     */
    protected static function likeModel(User_model $user_model, $model): void
    {
        if ($user_model->get_likes_balance() === 0) throw new \Exception('User haven\'t enough like');

        try {
            \App::get_s()->start_trans();

            $user_model->set_likes_balance($user_model->get_likes_balance() - 1);

            $model->set_likes($model->get_likes() + 1);

            \App::get_s()->commit();
        } catch (\Exception $e) {
            \App::get_s()->rollback();
            // TODO надо в какой-то логгер запихнуть
            throw $e;
        }
    }
}