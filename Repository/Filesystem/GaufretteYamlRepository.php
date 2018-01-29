<?php
namespace Devture\Bundle\EmailTemplateBundle\Repository\Filesystem;

use Devture\Component\DBAL\Model\BaseModel;
use Devture\Component\DBAL\Repository\BaseRepository;
use Symfony\Component\Yaml\Yaml;

/**
 * A repository backend by a Gaufrette filesystem (usually local) and serialized as YAML.
 *
 * Reason: we don't see email templates as dynamic data that should be database-specific.
 * We'd like to store email templates as part of the project source code, under version control.
 *
 * Why YAML?
 * Because we want to have better diffs for email text contents (multi-line HTML).
 * JSON encodes the whole content as one big long line with \n inside, which is not diff-friendly.
 * If it weren't for that, this ungodly YAML format wouldn't have been used.
 *
 * But using the filesystem like this is slow and unsafe?
 * True. But it doesn't matter.
 * This isn't meant to be used in a high-concurency system which manages thousands of templates.
 */
abstract class GaufretteYamlRepository extends BaseRepository {

	private $filesystem;

	public function __construct(\Gaufrette\Filesystem $filesystem) {
		$this->filesystem = $filesystem;
	}

	/**
	 * @param BaseModel $entity
	 * @see \Devture\Component\DBAL\Repository\RepositoryInterface::add()
	 */
	public function add($entity) {
		$yaml = $this->dumpAsYaml($this->exportModel($entity));
		$this->filesystem->write($this->transformIdToKey($entity->getId()), $yaml, false);
	}

	/**
	 * @param BaseModel $entity
	 * @see \Devture\Component\DBAL\Repository\RepositoryInterface::update()
	 */
	public function update($entity) {
		$yaml = $this->dumpAsYaml($this->exportModel($entity));

		$this->filesystem->write($this->transformIdToKey($entity->getId()), $yaml, true);
	}

	/**
	 * @param BaseModel $entity
	 * @see \Devture\Component\DBAL\Repository\RepositoryInterface::delete()
	 */
	public function delete($entity) {
		$this->filesystem->delete($this->transformIdToKey($entity->getId()));
	}

	/**
	 * @param mixed $id
	 * @throws \Devture\Component\DBAL\Exception\NotFound
	 * @return object
	 */
	public function find($id) {
		$stringId = (string) $id;
		if (isset($this->models[$stringId])) {
			return $this->models[$stringId];
		}

		try {
			$key = $this->transformIdToKey($id);
		} catch (\InvalidArgumentException $e) {
			throw new \Devture\Component\DBAL\Exception\NotFound('Cannot find, because of an invalid key for: ' . $id);
		}

		if (!$this->filesystem->has($key)) {
			throw new \Devture\Component\DBAL\Exception\NotFound('Cannot find: ' . $id);
		}

		$yaml = $this->filesystem->read($key);
		$data = Yaml::parse($yaml);
		return $this->createModel($data);
	}

	/**
	 * @return object[]
	 */
	public function findAll() {
		return array_map(function ($key) {
			$id = $this->transformKeyToId($key);
			return $this->find($id);
		}, $this->listKeys());
	}

	private function transformIdToKey($value) {
		$value = str_replace('/', '__sl__', $value);
		if (!preg_match("/^[a-z][a-z0-9\._\-]+$/", $value)) {
			throw new \InvalidArgumentException('Cannot handle id: ' . $value);
		}
		return $value;
	}

	private function transformKeyToId($value) {
		$value = str_replace('__sl__', '/', $value);
		return $value;
	}

	private function listKeys() {
		return array_filter($this->filesystem->listKeys()['keys'], function ($name) {
			return (strpos($name, '.') !== 0);
		});
	}

	private function dumpAsYaml(array $data): string {
		return Yaml::dump($data, 2, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
	}

}
