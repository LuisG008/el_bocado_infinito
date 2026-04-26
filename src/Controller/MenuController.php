<?php

namespace App\Controller;

use App\Entity\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use InvalidArgumentException;

use App\Service\MenuService;

#[Route('/api/menu')]
final class MenuController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Retorna todos los menús
     *
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-14
     */
    #[Route('', name: 'get_menu', methods: ['GET'])]
    public function menu(MenuService $menuService): Response
    {
        try {
            $data = $menuService->allMenus();
            
            
            return $this->json(['data' => $data]);

        } catch (\Throwable $th) {
            return $this->json($th->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Crea un nuevo menú
     *
     * @param Request $request
     * @param MenuService $menuService
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-04-26
     */
    #[Route('/create', name: 'create_menu', methods: ['POST'])]
    public function create(
        Request $request,
        MenuService $menuService
    ): Response {
        $Connection = $this->em->getConnection();
        $Connection->beginTransaction();

        try {
            $data = $request->request->all();
            // Capturar el archivo
            $imagenFile = $request->files->get('imagen');
           
            if($data['nombre'] == '' || $data['descripcion'] == '' ||  $data['precio'] == '' || $data['estado'] == ''){
                throw new InvalidArgumentException("Todos los campos son obligatorios", Response::HTTP_BAD_REQUEST);
            }

            $menuService->create($data, $imagenFile);

            $Connection->commit();
            return $this->json([
                'message' => 'Menu creado'
            ]);

        } catch (\Throwable $th) {
            $Connection->rollBack();
            
            return $this->json([
                'message' => $th->getMessage()
                ], $th->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Edita un menú
     *
     * @param Request $request
     * @param int $id
     * @param MenuService $menuService
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-04-26
     */
    #[Route('/edit/{id}', name: 'edit_menu', methods: ['POST'])]
    public function edit(
        Request $request, 
        int $id,
        MenuService $menuService
    ): Response {
        $Connection = $this->em->getConnection();
        $Connection->beginTransaction();

        try {
            $data = $request->request->all();

            // Capturar el archivo
            $imagenFile = $request->files->get('imagen');
            
            if($data['nombre'] == '' || $data['descripcion'] == '' ||  $data['precio'] == '' || $data['estado'] == ''){
                throw new InvalidArgumentException("Todos los campos son obligatorios", Response::HTTP_BAD_REQUEST);
            }

            $menuService->edit($id, $data, $imagenFile);

            $Connection->commit();
            return $this->json([
                'message' => 'Menu editado'
            ]);

        } catch (\Throwable $th) {
            $Connection->rollBack();
            dd($th);
            return $this->json([
                'message' => $th->getMessage()
                ], $th->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Busca un menu por su nombre o numero
     *
     * @param Request $request
     * @return Response
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-17
     */
    #[Route('/buscar', name: 'buscador_menu', methods: ['GET'])]
    public function buscar(Request $request): Response
    {
        $Connection = $this->em->getConnection();

        try {
            $texto = $request->query->get('texto', '');

            trim($texto);

            $data = $this->em->getRepository(Menu::class)->findByMenuText($texto);
            
            $data = $data ?: [];
            
            //$Connection->commit();
            return $this->json([
                'data' => $data
            ]);

        } catch (\Throwable $th) {
            //$Connection->rollBack();
            
            return $this->json([
                'message' => $th->getMessage()
                ], $th->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
