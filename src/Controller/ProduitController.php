<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Form\PanierType;
use App\Form\ProduitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProduitController extends AbstractController
{
    /**
     * @Route("/produit", name="produit")
     */
    public function index(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // recuperation de la photo
            $picture = $form->get('picture')->getData();

            // cas d'un produit sans photo
            if ($picture) {
                //
                $nomPicture = uniqid() . '.' . $picture->guessExtension();

                try {
                    //Upload de la photo
                    $picture->move(
                        $this->getParameter('upload_dir'),
                        $nomPicture
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', "Impossible d'uploader l'image");
                    return $this->redirectToRoute('produit');
                }

                $produit->setPicture($nomPicture);
            }

            $em->persist($produit);
            $em->flush(); //execute la requete 
            $this->addFlash("success", "Le produit a été ajouté");
        }
        $produits = $em->getRepository(Produit::class)->findAll();

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'form_ajout' => $form->createView(),
        ]);
    }

    /** 
     *@Route("/produit/{id}", name="un_produit")
     */

    public function produit(Request $request, Produit $produit = null, TranslatorInterface $translator)
    {
        if ($produit != null) {
            $panier = new Panier($produit);
            // si  le produit existe
            $form = $this->createForm(PanierType::class, $panier);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager(); // connexion à la bdd
                $em->persist($panier); // prepare
                $em->flush(); //execute
                $this->addFlash("success", $translator->trans('panier.added'));
            }
            return $this->render('produit/produit.html.twig', [
                'panier' => $panier,
                'produit' => $produit,
                'form_panier' => $form->createView(),
            ]);
        } else {
            // produit qui n'existe pas, on redirige l'internaute
            $this->addFlash('danger', 'Produit introuvable');
            return $this->redirectToRoute('produit');
        }
    }


    /**
     * @Route("/produit/delete/{id}", name="produit_delete")
     */
    public function delete(Produit $produit = null, TranslatorInterface $translator)
    {
        if ($produit != null) {
            // on a trouvé un produit , on le supprime
            $pdo = $this->getDoctrine()->getManager();
            $pdo->remove($produit);
            $pdo->flush();
            unlink($this->getParameter('upload_dir') . $produit->getPicture());
            $this->addFlash(
                "success",
                $translator->trans('produit.delete')
            );
        }
        return $this->redirectToRoute('home');
    }
}
