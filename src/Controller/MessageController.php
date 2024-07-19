<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\MessageStatus;
use App\Message\SendMessage;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @see MessageControllerTest
 * TODO: review both methods and also the `openapi.yaml` specification
 *       Add Comments for your Code-Review, so that the developer can understand why changes are needed.
 */
class MessageController extends AbstractController
{
    /**
     * TODO: cover this method with tests, and refactor the code (including other files that need to be refactored).
     */
    #[Route('/messages')]
    /** JsonResponse is more strict type hint for return type */
    /** Variable name refactor is just my preference, but it's all matter of adopted standards on the project */
    public function list(
        MessageRepository $messageRepository,
        /* new feature introduced in symfony 6.3 makes it easier to map query params */
        /* Nicolas Grekas in 7.1.* added additional validationFailedStatusCode option, which is really nice for enums since url is actually correct, but request
         * /** can not be comprehended by the server. Beside good practice, this is the reason why I updated symfony stack to 7.1.* */
        #[MapQueryParameter(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] ?MessageStatus $status = null
    ): JsonResponse {
        /** Unnecessary passing of entire request object. We should pass only what is going to be used. It will be more strict, stable, easier to test. */
        $messages = $messageRepository->by($status);

        /* removed obsolete transformation to array, which should be done in repository or service layer depending on requirements */

        /* JsonResponse handles json responses, which was desired here, judging the previous version of return statement */
        return new JsonResponse([
            'messages' => $messages,
        ]);
    }

    #[Route('/messages/send', methods: ['GET'])]
    public function send(Request $request, MessageBusInterface $bus): Response
    {
        $text = $request->query->get('text');

        if (!$text) {
            return new Response('Text is required', 400);
        }

        $bus->dispatch(new SendMessage($text));

        return new Response('Successfully sent', 204);
    }
}
