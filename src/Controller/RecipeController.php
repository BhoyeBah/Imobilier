<?php

namespace App\Controller;

use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use App\Entity\Recipe; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;



class RecipeController extends AbstractController
{
    #[Route('/recipe', name: 'recipe.index')]
    public function index(Request $request, RecipeRepository $repository, EntityManagerInterface $em): Response
    {
    
        // $recipes =$repository->findAll();
        $recipes = $repository->findWithDurationLowerThan(100);
        
        $em->remove($recipes[0]);
        return $this->render('recipe/index.html.twig',[
            'recipes'=>$recipes,
        ]);
    }

    #[Route('/recipe/{slug}-{id}', name: 'recipe.show', requirements: ['id' =>'\d+', 'slug' =>'[a-zA-Z0-9-]+'])]
    public function show(Request $request, string $slug, int $id,RecipeRepository $repository): Response
    {
        $recipe = $repository->find($id);

        if($recipe ->getSlug() != $slug){

           return $this->RedirectToRoute('recipe.show',['slug'=> $recipe->getSlug(), 'id'=> $recipe->getId()]);

        }
        return $this->render('recipe/show.html.twig',[
            'recipe'=> $recipe,
        ]);
    }

    #[Route('/recipe/{id}/edit', name: 'recipe.edit', methods:['POST', 'GET'])]
    public function edit(Request $request,Recipe $recipe,EntityManagerInterface $em){

        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($recipe);
            $em->flush();
            $this->addFlash('succes', 'La recette a bien été modifiéé');

            return $this->redirectToRoute('recipe.index');
        }
        return $this->render('recipe/edit.html.twig',[
            'recipe'=> $recipe,
             'form' => $form
        ]);
    }

    #[Route('/recipe/create', name:'recipe.create')]
    public function create(Request $request,EntityManagerInterface $em)
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success', 'La recette a bien étée créée');
            return $this->redirectToRoute('recipe.index');

        }
        return $this->render('recipe/create.html.twig',[
            'form'=> $form
            ]);

    }
    #[Route('/recipe/{id}/edit', name: 'recipe.delete', methods: ['DELETE'] )]
    public function remove(Recipe $recipe,EntityManagerInterface $em)
    {
        $this->remove($recipe);
        $em->flush();
        $this->addFlash('success', 'La recette a ete bien supprime');
        return $this->redirectToRoute('recipe.index');
    }


}
