<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Address;
use App\Repository\AddressRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/profile')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_profile')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('user/profile.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/edit', name: 'app_profile_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $user->setFirstName($request->request->get('first_name'));
            $user->setLastName($request->request->get('last_name'));
            $user->setPhone($request->request->get('phone'));

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/addresses', name: 'app_addresses')]
    public function addresses(AddressRepository $addressRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $addresses = $addressRepository->findBy(['user' => $this->getUser()]);

        return $this->render('user/addresses.html.twig', [
            'addresses' => $addresses,
        ]);
    }

    #[Route('/addresses/new', name: 'app_address_new')]
    public function newAddress(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($request->isMethod('POST')) {
            $address = new Address();
            $address->setUser($this->getUser());
            $address->setFirstName($request->request->get('first_name'));
            $address->setLastName($request->request->get('last_name'));
            $address->setStreet($request->request->get('street'));
            $address->setStreet2($request->request->get('street2'));
            $address->setCity($request->request->get('city'));
            $address->setState($request->request->get('state'));
            $address->setPostalCode($request->request->get('postal_code'));
            $address->setCountry($request->request->get('country'));
            $address->setPhone($request->request->get('phone'));

            $entityManager->persist($address);
            $entityManager->flush();

            $this->addFlash('success', 'Address added successfully');

            return $this->redirectToRoute('app_addresses');
        }

        return $this->render('user/address_form.html.twig');
    }

    #[Route('/addresses/{id}/edit', name: 'app_address_edit', requirements: ['id' => '\d+'])]
    public function editAddress(Address $address, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($address->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only edit your own addresses');
        }

        if ($request->isMethod('POST')) {
            $address->setFirstName($request->request->get('first_name'));
            $address->setLastName($request->request->get('last_name'));
            $address->setStreet($request->request->get('street'));
            $address->setStreet2($request->request->get('street2'));
            $address->setCity($request->request->get('city'));
            $address->setState($request->request->get('state'));
            $address->setPostalCode($request->request->get('postal_code'));
            $address->setCountry($request->request->get('country'));
            $address->setPhone($request->request->get('phone'));

            $entityManager->persist($address);
            $entityManager->flush();

            $this->addFlash('success', 'Address updated successfully');

            return $this->redirectToRoute('app_addresses');
        }

        return $this->render('user/address_form.html.twig', [
            'address' => $address,
        ]);
    }

    #[Route('/addresses/{id}/delete', name: 'app_address_delete', requirements: ['id' => '\d+'])]
    public function deleteAddress(Address $address, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($address->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only delete your own addresses');
        }

        $entityManager->remove($address);
        $entityManager->flush();

        $this->addFlash('success', 'Address deleted successfully');

        return $this->redirectToRoute('app_addresses');
    }
} 