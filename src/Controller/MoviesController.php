<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Form\MovieFormType;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class MoviesController extends AbstractController
{
   private $em;
    private $movieRepository;
   public function __construct(MovieRepository $movieRepository, EntityManagerInterface $em) {
        $this->movieRepository = $movieRepository;
        $this->em = $em;
   }
    /* private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    } */


    #[Route('/movies', methods: ['GET'], name: 'main_movies')]
    public function index(): Response
    {
        $movies = $this->movieRepository->findAll();
        // dd($movies);
        // findAll() - SELECT * FROM movies;
        // find() - SELECT * from movies WHERE id = 5;
        // findBy() - SELECT * from movies ORDER BY id DESC
        // findOneBy() - SELECT * FROM movies WHERE id = 6 AND title = The Dark Knight ORDER BY id DESC
        // count() - SELECT COUNT() from movies WHERE id = 1

        /* $repository = $this->em->getRepository(Movie::class);

        $movies = $repository->getClassName();

        dd($movies);
        */

        return $this->render('movies/index.html.twig', [
            'movies' => $movies
        ]);
    }

    #[Route('/movies/create', name: 'create_movie')]
    public function create(Request $request): Response // request allows for all user submits through the form
    {
        $movie = new Movie(); // creates a new class instance of Movie
        $form = $this->createForm(MovieFormType::class, $movie); // this will create data into a format which could then be used inside a twig template

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { // isSubmitted to check if GET or POST is submitted and isValid checks to see if fields are valid
            $newMovie = $form->getData(); // get data from the form in POST

            $imagePath = $form->get('imagePath')->getData();
            if ($imagePath) {
                $newFileName = uniqid() . '.' . $imagePath->guessExtension();

                try {
                    $imagePath->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $newMovie->setImagePath('/uploads/' . $newFileName); // method is from MovieRepository
            }

            $this->em->persist($newMovie);
            $this->em->flush();

            return $this->redirectToRoute('main_movies');
        }
        return $this->render('movies/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/movies/edit/{id}', name:'edit_movies')]
    public function edit($id, Request $request): Response
    {
        $movie = $this->movieRepository->find($id);
        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request); // handle request to database
        $imagePath = $form->get('imagePath')->getData();    //grab the imagepath value and only get the data from it

        // will need to see why this section up to line 123 does not work. button does not save the imagepath
        if($form->isSubmitted() && $form->isValid()) {
            if($imagePath) {    
                if($movie->getImagePath() !== null) {
                    if(file_exists($this->getParameter('kernel.project_dir') . $movie->getImagePath())) 
                    {
                            $this->getParameter('kernel.project_dir') . $movie->getImagePath();

                            $newFileName = uniqid() . '.' . $imagePath->guessExtension();

                            try {
                                $imagePath->move(
                                    $this->getParameter('kernel.project_dir') . '/public/uploads',
                                    $newFileName
                                );
                            } catch (FileException $e) {
                                return new Response($e->getMessage());
                            }

                            $movie->setImagePath('/uploads/' . $newFileName);

                            $this->em->flush();
                            return $this->redirectToRoute('create_movie');
                        }
                }
            } else {
                $movie->setTitle($form->get('title')->getData()); //set method to use the form data under title with getData method converting the data into the type it needs
                $movie->setReleaseYear($form->get('releaseYear')->getData());
                $movie->setDescription($form->get('description')->getData());

                $this->em->flush();
                return $this->redirectToRoute('main_movies');
            }
        }

        return $this->render('movies/edit.html.twig', [
            'movie' => $movie,
            'form' => $form->createView()
        ]);
    }

    #[Route('/movies/{id}', methods: ['GET'], name: 'movies')]
    public function show($id): Response
    {
        $movie = $this->movieRepository->find($id);

        return $this->render('movies/show.html.twig', [
            'movie' => $movie
        ]);
    }


    public function notifications(): Response
    {
        // get the user information and notifications somehow
        $userFirstName = 'Semisi';
        $userNotifications = ['first notification', 'second notification', 'third notification'];

        // the template path is the relative file path from `templates/`
        return $this->render('user/notifications.html.twig', [
            // this array defines the variables passed to the template,
            // where the key is the variable name and the value is the variable value
            // (Twig recommends using snake_case variable names: 'foo_bar' instead of 'fooBar')
            'user_first_name' => $userFirstName,
            'notifications' => $userNotifications,
        ]);
    }

}
