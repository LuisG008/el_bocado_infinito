<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\Menu;

class MenuService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Retorna todos los menús
     *
     * @return array
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-03-31
     */
    public function allMenus(): array
    {
        $menu = $this->em->getRepository(Menu::class)->findAll();

        return $menu;
    }

    /**
     * Crea un nuevo menú
     *
     * @param array $data
     * @param UploadedFile $imagenFile
     * @return Menu
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-04-26
     */
    public function create(array $data, UploadedFile $imagenFile): Menu
    {
        $rutaImagen = null;
            
        if ($imagenFile) {
            $nombreArchivo = uniqid() . '_' . $imagenFile->getClientOriginalName();
            $local = 'almacenamiento/menu/' . date('Y-m') . '/';
            $rutaImagen = $local . $nombreArchivo;
            $imagenFile->move($local, $nombreArchivo);
        }

        $Menu = new Menu();

        $Menu->setNombre($data['nombre']);
        $Menu->setDescripcion($data['descripcion']);
        $Menu->setPrecio($data['precio']);
        $Menu->setRutaImagen($rutaImagen);
        $Menu->setEstado($data['estado']);

        $this->em->persist($Menu);
        $this->em->flush();

        return $Menu;
    }

    /**
     * Edita un menú
     *
     * @param int $id
     * @param array $data
     * @param UploadedFile|null $imagenFile
     * @return Menu
     * @author Luis Sanchez <betancurluis20@gmail.com> 2026-04-26
     */
    public function edit(int $id, array $data, ?UploadedFile $imagenFile = null): Menu
    {
        $Menu = $this->em->getRepository(Menu::class)->find($id);

        if (!$Menu) {
            throw new InvalidArgumentException("El menú con el ID proporcionado no existe", Response::HTTP_NOT_FOUND);
        }
        
        if ($imagenFile) {
            $nombreArchivo = uniqid() . '_' . $imagenFile->getClientOriginalName();
            $local = 'almacenamiento/menu/' . date('Y-m') . '/';
            $rutaImagen = $local . $nombreArchivo;
            $imagenFile->move($local, $nombreArchivo);

            $Menu->setRutaImagen($rutaImagen);
        }

        $Menu->setNombre($data['nombre']);
        $Menu->setDescripcion($data['descripcion']);
        $Menu->setPrecio($data['precio']);
        $Menu->setEstado($data['estado']);

        $this->em->flush();

        return $Menu;
    }

    public function prePedidos(array $idsPedidos): array
    {
        $pedidos = $this->em->getRepository(Menu::class)->findByIds($idsPedidos);

        return $pedidos;
    }
}