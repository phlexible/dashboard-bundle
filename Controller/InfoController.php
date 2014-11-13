<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\DashboardBundle\Controller;

use Phlexible\Bundle\SecurityBundle\Acl\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Info controller
 *
 * @author Stephan Wentz <sw@brainbits.net>
 * @Route("/dashboard/info")
 */
class InfoController extends Controller
{
    /**
     * Return info
     *
     * @param Request $request
     *
     * @return Response
     * @Route("", name="dashboard_info")
     */
    public function infoAction(Request $request)
    {
        $securityContext = $this->get('security.context');

        $lines = [];

        if ($securityContext->isGranted('ROLE_SUPER_ADMIN')) {
            $lines[] = [
                'Project:',
                $this->container->getParameter('phlexible_gui.project.title') . ' '
                    . $this->container->getParameter('phlexible_gui.project.version')
            ];
            $lines[] = [
                'Env:',
                $this->container->getParameter('kernel.environment') . ($this->container->getParameter(
                    'kernel.debug'
                ) ? ' [DEBUG]' : '')
            ];
            $lines[] = ['Host:', $request->server->get('SERVER_NAME') . ' [' . PHP_SAPI . ']'];

            $connection = $this->getDoctrine()->getConnection();
            /* @var $connection \Doctrine\DBAL\Connection */

            $lines[] = [
                'Default Database:',
                $connection->getHost() . ' / ' . $connection->getDatabase() . ' [' . $connection->getDriver()->getName(
                ) . ']'
            ];

            $lines[] = ['Session:', $request->getSession()->getId() . ' [' . $_SERVER['REMOTE_ADDR'] . ']'];

            $lines[] = [
                'User:',
                $this->getUser()->getUsername() . ' [' . implode(', ', $this->getUser()->getRoles()) . ']'
            ];

            $lines[] = ['UserAgent:', $request->server->get('HTTP_USER_AGENT')];
        } else {
            $lines[] = [
                'Project:',
                $this->container->getParameter('phlexible_gui.project.title') . ' '
                . $this->container->getParameter('phlexible_gui.project.version')
            ];
        }

        $l1 = 0;
        $l2 = 0;
        foreach ($lines as $line) {
            if (strlen($line[0]) > $l1) {
                $l1 = strlen($line[0]);
            }
            if (isset($line[1]) && strlen($line[1]) > $l2) {
                $l2 = strlen($line[1]);
            }
        }
        $table = '';
        foreach ($lines as $line) {
            $table .= str_pad($line[0], $l1 + 2);
            $table .= str_pad($line[1], $l2 + 2);
            if (isset($line[2])) {
                $table .= $line[2];
            }
            $table .= PHP_EOL;
        }
        $out = '<pre>' . $table . '</pre>';

        return new Response($out);
    }
}
