<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\UsuarioAgregaUsuario;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Routing\Annotation\Route;

class UsuarioController extends AbstractController
{
    #[Route("/api/login", name: "login", methods: ["POST"])]
    public function login
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
                    'token' => $token,

                    'id' => $usuario->getId(),
                    'mail' => $usuario->getMail(),
                    'usuario' => $usuario->getUsuario(),
                    'verificado' => $usuario->isVerificado(),
                    'nombre' => $usuario->getNombre(),
                    'apellido_1' => $usuario->getApellido1(),
                    'apellido_2' => $usuario->getApellido2(),
                    'fechaNacimiento' => $usuario->getFechaNacimiento(),
                    'pais' => $usuario->getPais(),
                    'profesion' => $usuario->getProfesion(),
                    'estudios' => $usuario->getEstudios(),
                    'idioma' => $usuario->getIdioma(),
                    'permiso' => $usuario->getPermiso()

                ],
                Response::HTTP_OK
            );
        }        

        return $respuestaJson;
    }

    #[Route("/api/registro", name: "registro", methods: ["POST"])]
    public function registro
    (
        Request $request, 
        EntityManagerInterface $entityManager
    )
    {
        $respuestaJson = null;
        $datosRecibidos = json_decode($request->getContent(), true);

        $nombreUsuario = $datosRecibidos['usuario'];
        $mail = $datosRecibidos['mail'];
        $contrasenia = $datosRecibidos['contrasenia'];

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

    #[Route("/api/validar-token", name: "validar_token", methods: ["POST"])]
    public function validarToken
    (
        Request $request, 
        JWTTokenManagerInterface $JWTManager
    )
    {
        $datosRecibidos = json_decode($request->getContent(), true);
        $token = $datosRecibidos['token'];
        $respuestaJson = null;

        try 
        {
            $JWTManager->parse($token);

            $respuestaJson = new JsonResponse
            (
                [
                    "exito" => true,
                    "mensaje" => "Token válido."
                ],
                Response::HTTP_OK
            );
        } 
        catch (\Exception $e) 
        {
            $respuestaJson = new JsonResponse
            (
                [
                    "exito" => false,
                    "mensaje" => "Token inválido."
                ],
                Response::HTTP_UNAUTHORIZED
            );
        }

        return $respuestaJson;
    }

    #[Route("/api/agregar-usuario", name: "agregar_usuario", methods: ["POST"])]
    public function agregarUsuario
    (
        Request $request, 
        EntityManagerInterface $entityManager
    )
    {
        $respuestaJson = null;
        
        $datosRecibidos = json_decode($request->getContent(), true);
        $nombreUsuarioAgregar = $datosRecibidos['usuarioAgregar'];
        $usuarioID = $datosRecibidos['usuarioID'];  

        $usuario_1 = $entityManager->
            getRepository(Usuario::class)->find($usuarioID);
        $usuario_2 = $entityManager->
            getRepository(Usuario::class)->findOneBy(['usuario' => $nombreUsuarioAgregar]);

        if ($usuario_1 === $usuario_2) 
        {
            return new JsonResponse
            (
                [
                    "exito" => false,
                    "mensaje" => "No puedes agregarte a ti mismo."
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $usuarioAgregaUsuario = new UsuarioAgregaUsuario();
        $usuarioAgregaUsuario->setUsuario1($usuario_1);
        $usuarioAgregaUsuario->setUsuario2($usuario_2);

        try 
        {
            $entityManager->persist($usuarioAgregaUsuario);
            $entityManager->flush();
            
            $respuestaJson = new JsonResponse
            (
                [
                    "exito" => true,
                    "mensaje" => "Petición de amistad enviada."
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
                    "mensaje" => "Petición de amistad ya enviada o usuario inexistente."
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $respuestaJson;
    }

    #[Route("/api/modificar-datos-usuario", name: "modificar_datos_usuario", methods: ["POST"])]
    public function modificarDatosUsuario
    (
        Request $request, 
        EntityManagerInterface $entityManager
    )
    {
        $respuestaJson = null;
        $datosRecibidos = json_decode($request->getContent(), true);

        $id = $datosRecibidos['id'];
        $usuario = $entityManager->getRepository(Usuario::class)->find($id);
        if (!$usuario) 
        {
            return new JsonResponse
            (
                [
                    "exito" => false,
                    "mensaje" => "Modificación del usuario fallida."
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        $contrasenia_actual = $datosRecibidos['contrasenia_actual'];
        if (!password_verify($contrasenia_actual, $usuario->getContrasenia())) 
        {
            return new JsonResponse
            (
                [
                    "exito" => false,
                    "mensaje" => "Contraseña actual incorrecta."
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $mail = $datosRecibidos['mail'];
        $nombreUsuario = $datosRecibidos['usuario'];
        $nombre = $datosRecibidos['nombre'];
        $apellido_1 = $datosRecibidos['apellido_1'];
        $apellido_2 = $datosRecibidos['apellido_2'];
        $pais = $datosRecibidos['pais'];
        $idioma = $datosRecibidos['idioma'];
        $profesion = $datosRecibidos['profesion'];
        $fecha_nacimiento = $datosRecibidos['fecha_nacimiento'];
        $nueva_contrasenia = $datosRecibidos['nueva_contrasenia'];

        if (!empty($nombreUsuario)) 
        {
            $usuario->setUsuario($nombreUsuario);
        }
        if (!empty($mail)) 
        {
            $usuario->setMail($mail);
        }
        if (!empty($nombre)) 
        {
            $usuario->setNombre($nombre);
        }
        if (!empty($apellido_1)) 
        {
            $usuario->setApellido1($apellido_1);
        }
        if (!empty($apellido_2)) 
        {
            $usuario->setApellido2($apellido_2);
        }
        if (!empty($pais)) 
        {
            $usuario->setPais($pais);
        }
        if (!empty($idioma)) 
        {
            $usuario->setIdioma($idioma);
        }
        if (!empty($profesion))
        {
            $usuario->setProfesion($profesion);
        }
        if (!empty($fecha_nacimiento)) 
        {
            try 
            {
                $fechaNacimientoFormateada = new \DateTime($fecha_nacimiento);
            } 
            catch (\Exception $e) 
            {
                $fechaNacimientoFormateada = null;
            }

            $usuario->setFechaNacimiento($fechaNacimientoFormateada);
        }
        if (!empty($nueva_contrasenia)) 
        {
            $usuario->setContrasenia($nueva_contrasenia);
        }

        try 
        {
            $entityManager->persist($usuario);
            $entityManager->flush();
        
            $respuestaJson = new JsonResponse
            (
                [
                    "exito" => true,
                    "mensaje" => "Usuario modificado exitosamente."
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
                    "mensaje" => "Modificación del usuario fallida."
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $respuestaJson;
    }

    #[Route("/api/obtener-solicitudes", name: "obtener_solicitudes", methods: ["GET"])]
    public function obtenerSolicitudes
    (
        Request $request, 
        EntityManagerInterface $entityManager
    )
    {
        $respuestaJson = null;
        $datosRecibidos = json_decode($request->getContent(), true);
        $usuarioId = $datosRecibidos['id'];
    
        try {
            // Obtener las solicitudes de amistad donde usuario_2_id coincide con el ID proporcionado
            $solicitudesAmistad = $entityManager->getRepository(UsuarioAgregaUsuario::class)->findBy(['usuario_2' => $usuarioId]);
    
            $dataAmistad = [];
            $dataLista = []; // Mantener la estructura para futuras implementaciones
    
            foreach ($solicitudesAmistad as $solicitud) {
                // Verificar que no exista un registro con los IDs intercambiados
                $solicitudAceptada = $entityManager->getRepository(UsuarioAgregaUsuario::class)->findOneBy([
                    'usuario_1' => $usuarioId,
                    'usuario_2' => $solicitud->getUsuario1()->getId()
                ]);
    
                if (!$solicitudAceptada) {
                    $dataAmistad[] = [
                        'nombre' => $solicitud->getUsuario1()->getUsuario(), // Asumiendo que 'usuario' es el nombre de usuario
                        'tipo' => 'amistad',
                    ];
                }
            }
    
            if (empty($dataAmistad) && empty($dataLista)) {
                $respuestaJson = new JsonResponse(
                    [
                        "exito" => false,
                        "mensaje" => "No existen solicitudes."
                    ],
                    Response::HTTP_OK
                );
            } else {
                $respuestaJson = new JsonResponse(
                    [
                        "exito" => true,
                        "mensaje" => "Solicitudes obtenidas exitosamente.",
                        "data" => [
                            "amistad" => $dataAmistad,
                            "lista" => $dataLista // Mantener la estructura para futuras implementaciones
                        ]
                    ],
                    Response::HTTP_OK
                );
            }
        } catch (\Throwable $th) {
            $respuestaJson = new JsonResponse(
                [
                    "exito" => false,
                    "mensaje" => "No se han podido obtener las solicitudes."
                ],
                Response::HTTP_BAD_REQUEST
            );
        }
    
        return $respuestaJson;
    }
}