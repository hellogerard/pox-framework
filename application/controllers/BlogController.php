<?php

class BlogController extends Controller
{
    public function index()
    {
        $posts = $this->_factory->get('Post_All');

        //$posts->pageNo = 1;
        //$posts->pageSize = 16;

        $this->view->posts = $posts->posts;
        $this->view->title = 'My Blog';
        $this->view->display('blog/posts.tpl');
    }

    public function view($id)
    {
        if ($_POST)
        {
            $this->_logger->debug("comment submitted for post $id");

            $validators = array(
                'name' => array(
                    'NotEmpty',
                    'messages' => 'Name is required'
                ),
                'aComment' => array(
                    'NotEmpty',
                    'messages' => 'Comment is required'
                )
            );

            $form = new Form($validators, $_POST);

            if (! $form->isValid())
            {
                $this->_logger->debug("comment submitted but invalid");

                $this->view->assign($_POST);
                $this->view->assign($form->getMessages());
            }
            else
            {
                $this->_logger->debug("comment submitted successfully");

                $comment = $this->_factory->get('Post_Comment');
                $comment->post_id = $id;
                $comment->name = $form->getHTMLEntities('name');
                $comment->comment = $form->getHTMLEntities('aComment');
                $comment->create_dt_tm = date('Y-m-d H:i:s');
                $comment->save();
                header("Location: /blog/view/$id");
                exit;
            }
        }

        $post = $this->_factory->get('Post', $id);
        $this->view->post = $post;
        $this->view->display('blog/post.tpl');
    }

    public function delete($id)
    {
        $this->_logger->debug("deleting comment $id");

        $post = $this->_factory->get('Post', $id);
        $post->delete();
        header('Location: /blog');
        exit;
    }

    public function post($id = null)
    {
        if (empty($_POST))
        {
            $this->_logger->debug("loading form at {$_SERVER['REQUEST_URI']}");

            if ($id !== null)
            {
                $post = $this->_factory->get('Post', $id);
                $this->view->aTitle = $post->title;
                $this->view->aBody = $post->body;
            }

            $this->view->display('blog/edit.tpl');
        }
        else
        {
            $this->_logger->debug("form at {$_SERVER['REQUEST_URI']} submitted");

            $validators = array(
                'aTitle' => array(
                    'NotEmpty',
                    'messages' => 'Title is required'
                ),
                'aBody' => array(
                    'NotEmpty',
                    'messages' => 'Body is required'
                )
            );

            $form = new Form($validators, $_POST);

            if (! $form->isValid())
            {
                $this->_logger->debug("form at {$_SERVER['REQUEST_URI']} submitted but invalid");

                $this->view->assign($_POST);
                $this->view->assign($form->getMessages());
                $this->view->display('blog/edit.tpl');
            }
            else
            {
                $this->_logger->debug("form at {$_SERVER['REQUEST_URI']} successful ");

                $post = $this->_factory->get('Post', $id);
                $post->title = $form->getHTMLEntities('aTitle');
                $post->body = $form->getHTMLEntities('aBody');
                $post->create_dt_tm = date('Y-m-d H:i:s');
                $post->save();
                header('Location: /blog');
                exit;
            }
        }
    }
}

