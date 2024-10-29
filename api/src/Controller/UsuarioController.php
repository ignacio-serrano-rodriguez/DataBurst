<?php

namespace App\Controller;

use App\Repository\UsuarioRepository;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Routing\Annotation\Route;

class UsuarioController extends AbstractController
{
    #[Route("/api/auth/signin", name: "signin", methods: ["POST"])]
    public function signin
    (
        Request $request, 
        UsuarioRepository $usuarioRepository, 
        JWTTokenManagerInterface $JWTManager
    ) 
    {

        $datosRecibidos = json_decode($request->getContent(), true);
        $nombreUsuario = $datosRecibidos['usuario'];
        $contrasenia = $datosRecibidos['contrasenia'];
        $respuestaJson = null;

        $usuario = $usuarioRepository->findOneBy(['usuario' => $nombreUsuario]);

        if (!$usuario) 
        {
            $respuestaJson = new JsonResponse
            (
                [
                    "exito" => false,
                    "mensaje" => "Inicio de sesión fallido.",
                    'token' => ''
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        else if (!password_verify($contrasenia, $usuario->getContrasenia())) 
        {
            $respuestaJson = new JsonResponse
            (
                [
                    "exito" => false,
                    "mensaje" => "Inicio de sesión fallido.",
                    'token' => ''
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        else 
        {
            $token = $JWTManager->create($usuario);
            
            $respuestaJson = new JsonResponse
            (
                [
                    "exito" => true,
                    "mensaje" => "Inicio de sesión exitoso.",
                    'token' => $token
                ],
                Response::HTTP_OK
            );
        }        

        return $respuestaJson;
    }

    #[Route("/api/auth/signup", name: "signup", methods: ["POST"])]
    public function signup(Request $request, EntityManagerInterface $entityManager)
    {
        $datosRecibidos = json_decode($request->getContent(), true);
        $nombreUsuario = $datosRecibidos['usuario'];
        $mail = $datosRecibidos['mail'];
        $contrasenia = $datosRecibidos['contrasenia'];
        $respuestaJson = null;

        $usuario = new Usuario();
        $usuario->setUsuario($nombreUsuario);
        $usuario->setMail($mail);
        $usuario->setContrasenia($contrasenia);

        try 
        {
            $entityManager->persist($usuario);
            $entityManager->flush();
        
            $respuestaJson = new JsonResponse
            (
                [
                    "exito" => true,
                    "mensaje" => "Usuario creado exitosamente."
                ],
                Response::HTTP_CREATED
            );
        } 
        
        catch (\Throwable $th) 
        {
            $respuestaJson = new JsonResponse
            (
                [
                    "exito" => false,
                    "mensaje" => "Registro fallido."
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $respuestaJson;
    }
}