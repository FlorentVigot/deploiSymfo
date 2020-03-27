<?php

namespace App\Controller;

use App\Entity\Panier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PanierController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $panier = $em->getRepository(Panier::class)->findAll();
        return $this->render('panier/index.html.twig', [
            'panier' => $panier,

        ]);
    }

    /**
     * @Route("/panier/delete/{id}", name="delete_produit_from_panier")
     */

    public function delete(Panier $produit = null, TranslatorInterface $translator)
    {
        if ($produit != null) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($produit);
            $em->flush();
            $this->addFlash("success", $translator->trans('panier.supprimer'));
        } else {
            $this->addFlash("danger", $translator->trans('panier.passupprimer'));
        }
        return $this->redirectToRoute('home');
    }

    // Fontion qui permet de passer l'etat de 1 à 0
    /**
     * @Route("/panier/acheter", name="panier_acheter")
     */
    public function panierAcheter(TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $paniers = $em->getRepository(Panier::class)->findAll();
        if ($paniers != null) {
            foreach ($paniers as $panier) {
                $panier->setState(true);
            }
            $em->persist($panier);
            $em->flush();
            $this->addFlash("success", $translator->trans('panier.acheter'));
        } else {
            $this->addFlash("danger", $translator->trans('panier.pasacheter'));
        }
        return $this->redirectToRoute('home');
    }
}
