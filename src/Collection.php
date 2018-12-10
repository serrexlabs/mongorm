<?php

namespace Serrexlabs\Mongorm;


use MongoDB\BSON\ObjectID;
use MongoDB\Collection as MongoCollection;
use MongoDB\Driver\Cursor;
use MongoDB\Model\BSONDocument;

abstract class Collection extends MongoCollection
{
    /**
     * Collection constructor.
     */
    public function __construct()
    {
        $this->validateDependency();

        $db = Mongo::get();
        $dbName = Mongo::getDatabase();

        $collectionName = $this->getCollectionName();
        parent::__construct($db->getManager(), $dbName, $collectionName);
    }

    /**
     * @param string $id
     *
     * @return Entity
     */
    public function findById($id)
    {
        return $this->findOne(["_id" => new ObjectID($id)]);
    }

    /**
     * @inheritdoc
     */
    public function findOne($filter = [], array $options = [])
    {
        $result = parent::findOne($filter, $options);
        return $this->bind($result);
    }


    /**
     * @param array $filter
     * @param array $options
     * @return array
     */
    public function findMany($filter = [], array $options = [])
    {
        $entities = [];
        $results = parent::find($filter, $options);
        foreach ($results as $entity) {
            $entities[] = $this->bind($entity);
        }
        return $entities;
    }

    /**
     * @param $document
     * @return \MongoDB\InsertOneResult
     * @throws \ReflectionException
     */
    public function insert($document)
    {
        if ($document instanceof Entity) {
            $document = $document->toArray();
            unset($document['id']);
            foreach($document as $index => $attribute) {
                if ($attribute == null) {
                    unset($document[$index]);
                }
            }
        }
        return parent::insertOne($document);
    }

    /**
     * @inheritdoc
     */
    public function insertMany(array $entities, array $options =[])
    {
        $entitiesArray = [];
        foreach ($entities as $entity) {
            $entitiesArray[] = $entity->toArray();
        }
        return parent::insertMany($entitiesArray);
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        $collectionName = str_plural(snake_case($this->getCollectionNameWithoutPostfix()));
        return $collectionName;
    }

    protected function bind($result)
    {
        if ($result instanceof BSONDocument) {
            return $this->bindOne($result);
        }

        if ($result instanceof Cursor) {
            return $this->bindMany($result);
        }

        return $result;
    }

    protected function resolveEntity()
    {
        $entityName = $this->entityName();
        return new $entityName;
    }

    private function bindOne($result)
    {
        $entity = $this->resolveEntity();
        foreach ($result as $property => $value) {
            $this->setProperty($entity, $property, $value);
        }
        return $entity;
    }

    private function bindMany($results)
    {
        $entities = [];
        foreach ($results as $result) {
            $entities[] = $this->bindOne($result);
        }
        return $entities;
    }

    private function setProperty($entity, $property, $value)
    {
        if ($property === "_id") {
            $property = "id";
        }
        $property = str_replace('_', '', lcfirst(ucwords($property, '_')));
        $setter = "set" . ucfirst($property);
        $entity->$setter($value);
    }

    private function validateDependency()
    {
        if (!class_exists($this->entityName())) {
            throw new \RuntimeException("Entity not found (" . $this->entityName() . ")");
        }
    }

    /**
     * @return string
     */
    protected function entityName()
    {
        $entityName = $this->getCollectionNameWithoutPostfix();
        $entityName = config('mongorm.mongo.entity_namespace') . $entityName;
        return $entityName;
    }

    /**
     * @return mixed
     */
    protected function getCollectionNameWithoutPostfix()
    {
        $name = str_replace("Collection", "", class_basename($this));
        return $name;
    }
}