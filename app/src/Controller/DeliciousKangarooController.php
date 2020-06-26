<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DeliciousKangarooController extends AbstractController
{
    /**
     * @Route("/delicious/kangaroo", name="delicious_kangaroo")
     */
    public function index()
    {
        $id = 'Record1';
        $id = 'Site_5_Record_009--';
        if (preg_match('/^(.*)(Record)([-_])?(\d+)(.*)$/i', $id, $matches)) {
            $prefix = $matches[1];
            $recordWord = $matches[2];
            $separator = $matches[3];
            $number = $matches[4];
            $suffix = $matches[5];

            $newNumber = sprintf('%0' . strlen($number) . 'd', $number + 1);

            echo sprintf('%s%s%s%s%s', $prefix, $recordWord, $separator, $newNumber, $suffix);
            die;
        }
        //     die('aaa');
        // }

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/DeliciousKangarooController.php',
        ]);
    }
}
