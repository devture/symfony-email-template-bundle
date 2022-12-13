<?php
namespace Devture\Bundle\EmailTemplateBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Devture\Component\DBAL\Exception\NotFound;
use Devture\Component\Form\Token\TokenManagerInterface;
use Devture\Bundle\EmailTemplateBundle\Repository\EmailTemplateRepositoryInterface;
use Devture\Bundle\EmailTemplateBundle\Form\EmailTemplateFormBinder;

class ManagementController extends AbstractController {

	public function __construct(
		private array $locales,
		private bool $editable,
		private string $twigLayoutPath,
	) {
	}

	/**
	 * @Route("/manage", name="devture_email_template.manage", methods={"GET"})
	 */
	public function index(Request $request, EmailTemplateRepositoryInterface $repository) {
		return $this->render('@DevtureEmailTemplate/webui/index.html.twig', [
			'editable' => $this->editable,
			'twigLayoutPath' => $this->twigLayoutPath,
			'items' => $repository->findAll(),
		]);
	}

	/**
	 * @Route("/add", name="devture_email_template.add", methods={"GET", "POST"})
	 */
	public function add(
		Request $request,
		EmailTemplateRepositoryInterface $repository,
		EmailTemplateFormBinder $formBinder
	) {
		if (!$this->editable) {
			throw $this->createAccessDeniedException('Not editable');
		}

		$entity = $repository->createModel([]);

		if ($request->getMethod() === 'POST' && $formBinder->bind($entity, $request)) {
			$repository->add($entity);

			return $this->redirect($this->generateUrl('devture_email_template.edit', [
				'id' => $entity->getId(),
				'flashSuccess' => 1,
			]));
		}

		return $this->render('@DevtureEmailTemplate/webui/record.html.twig', [
			'twigLayoutPath' => $this->twigLayoutPath,
			'editable' => $this->editable,
			'locales' => $this->locales,

			'entity' => $entity,
			'isAdded' => false,
			'form' => $formBinder,
			'locales' => $this->locales,
			'flashSuccess' => $request->query->has('flashSuccess'),
		]);
	}

	/**
	 * @Route("/edit/{id}", name="devture_email_template.edit", methods={"GET", "POST"}, requirements={"id": ".+"})
	 */
	public function edit(
		Request $request,
		string $id,
		EmailTemplateRepositoryInterface $repository,
		EmailTemplateFormBinder $formBinder
	) {
		try {
			$entity = $repository->find($id);
		} catch (NotFound $e) {
			throw $this->createNotFoundException('Not found');
		}

		if ($request->getMethod() === 'POST') {
			if (!$this->editable) {
				throw $this->createAccessDeniedException('Not editable');
			}

			if ($formBinder->bind($entity, $request)) {
				$repository->update($entity);

				return $this->redirect($this->generateUrl('devture_email_template.edit', [
					'id' => $entity->getId(),
					'flashSuccess' => 1,
				]));
			}
		}

		return $this->render('@DevtureEmailTemplate/webui/record.html.twig', [
			'twigLayoutPath' => $this->twigLayoutPath,
			'editable' => $this->editable,
			'locales' => $this->locales,

			'entity' => $entity,
			'isAdded' => true,
			'form' => $formBinder,
			'flashSuccess' => $request->query->has('flashSuccess'),
		]);
	}

	/**
	 * @Route("/delete/{id}/{token}", name="devture_email_template.delete", methods={"POST"}, requirements={"id": ".+"})
	 */
	public function delete(
		Request $request,
		string $id,
		string $token,
		EmailTemplateRepositoryInterface $repository,
		TokenManagerInterface $tokenManager
	) {
		if (!$this->editable) {
			throw $this->createAccessDeniedException('Not editable');
		}

		$intention = 'delete-article-' . $id;
		if ($tokenManager->isValid($intention, $token)) {
			try {
				$repository->delete($repository->find($id));
			} catch (NotFound $e) {

			}
			return $this->json(['ok' => true]);
		}
		return $this->json([]);
	}

}
