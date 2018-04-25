<?php

namespace KRG\DoctrineExtensionBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/orm", name="krg_orm_admin_")
 */
class AdminController extends BaseAdminController
{
    /**
     * @Route("/download", name="download")
     */
    public function downloadAction()
    {
        $host = getenv('MYSQL_HOST');
        $user = getenv('MYSQL_USER');
        $password = getenv('MYSQL_PASSWORD');
        $database = getenv('MYSQL_DATABASE');

        $filename = sprintf('%s-%s.sql.gz', $database, date("d-m-Y"));

        header( "Content-Type: application/x-gzip" );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

        $cmd = "mysqldump -h $host -u $user --password=$password $database | gzip --best";

        passthru( $cmd );

        exit(0);
    }
}