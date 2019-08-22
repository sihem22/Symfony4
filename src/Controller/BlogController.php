<?php

namespace App\Controller;


use App\Form\ArticleType;
use App\Form\CommentType;
use phpDocumentor\Reflection\Types\Null_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Entity\Comment;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo)
    {
        $article = $repo->findAll();
        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $article
        ]);
    }


    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('blog/home.html.twig');
    }
    /**
 * @Route("/blog/new", name="blog_create")
 */
    public function create(Request $request, ObjectManager $manager)
    {
        if($request->request->count()>0)//s'il y a qque chose a l'interieur du post
        {
            $article= new Article();
            $article->setTitle($request->request->get('title'))
                ->setContent($request->request->get('content'))
                ->setImage($request->request->get('image'))
                ->setCreatedAt(new \DateTime());
            $manager->persist($article);
            $manager->flush();
            return $this->redirectToRoute('blog_show',['id'=>$article->getId()]);
        }
        //dump($request);
        return $this->render('blog/create.html.twig');
    }
    /**
     * @Route("/blog1/created", name="blog_created")
     * @Route("/blog1/{id}/edit", name="blog_edit")
     */
    public function created(Article $article = null, Request $request, ObjectManager $manager)
    {
           if(!$article){
               $article= new Article();

           }

          //  $article->setTitle("Titre d'exemple")
            //        ->setContent("Le contenu de l'article ");
          //  $form = $this->createFormBuilder($article)
             //            ->add('title')
              //           ->add('content')
               //          ->add('image')

                 //       ->getForm();
            $form = $this->createForm(ArticleType::class, $article);
            $form->handleRequest($request);
            if ($form->isSubmitted()&& $form->isValid()) {
                if(!$article->getId()) {
                    $article->setCreatedAt(new \DateTime());
            }

                $manager->persist($article);
                $manager->flush();
                return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
            }

        return $this->render('blog/created.html.twig',[
            'formArticle' =>$form->createView(),
            'editMode' => $article->getId() !== null
            ]);
    }
    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article, Request $request, ObjectManager $manager)
    {    $comment= new Comment();
         $form = $this->createForm(CommentType::class, $comment);
         $form->handleRequest($request);
         if($form->isSubmitted()&& $form->isValid())
         {
             $comment->setCreatedAt(new \DateTime())
                     ->setArticle($article);
             $manager->persist($comment);
             $manager->flush();
             return $this->redirectToRoute('blog_show', ['id'=> $article->getId()]);
         }
        return $this->render('blog/show.html.twig',
            ['article' => $article,
                'commentForm' => $form->createView()
            ]);
    }

}
