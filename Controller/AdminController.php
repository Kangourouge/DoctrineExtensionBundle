<?php

namespace KRG\DoctrineExtensionBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
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
        $host = $this->getParameter('database_host');
        $user = $this->getParameter('database_user');
        $password = $this->getParameter('database_password');
        $database = $this->getParameter('database_name');

        $filename = sprintf('%s-%s.sql.gz', $database, date("d-m-Y"));

        header("Content-Type: application/x-gzip");
        header("Content-Disposition: attachment; filename='$filename'");

        $cmd = "mysqldump -h $host -u $user --password=$password $database | gzip --best";

        passthru($cmd);

        exit(0);
    }
}
