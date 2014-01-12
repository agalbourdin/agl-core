<?php
namespace Agl\Core\Session\Storage;

use \Agl\Core\Agl,
    \Agl\Core\Db\Collection\Collection,
    \Agl\Core\Db\Query\Conditions\Conditions,
    \Agl\Core\Session\SessionAbstract,
    \Agl\Core\Session\SessionInterface;

/**
 * Methods to manage session with a database storage.
 *
 * @category Agl_Core
 * @package Agl_Core_Session_Storage
 * @version 0.1.0
 * @todo Test implementation
 */

class Db
	extends SessionAbstract
        implements SessionInterface
{
    /**
     * The database collection to store the sessions.
     */
    const DB_COLLECTION = 'session';

    /**
     * Database Items.
     *
     * @var array
     */
    private $_items = array();

    /**
     * Initialize the session and register the session handler methods.
     */
    public function __construct()
    {
        session_set_save_handler(array($this, '_open'),
                                 array($this, '_close'),
                                 array($this, '_read'),
                                 array($this, '_write'),
                                 array($this, '_destroy'),
                                 array($this, '_clean')
        );

        parent::__construct();
    }

    /**
     * Open the session.
     *
     * @return bool
     */
    public function _open()
    {
        return true;
    }

    /**
     * Close the session.
     *
     * @return bool
     */
    public function _close()
    {
        return true;
    }

    /**
     * Read the session identified by the ID parameter and return the session
     * data.
     *
     * @param string $pId Session id
     * @return string
     */
    public function _read($pId)
    {
        if (! isset($this->_items[$pId])) {
            $this->_items[$pId] = Agl::getModel(self::DB_COLLECTION);
            $this->_items[$pId]->loadByRealId($pId);
        }

        if ($this->_items[$pId]->getId()) {
            return $this->_items[$pId]->getData();
        }

        return '';
    }

    /**
     * Write the session to the database.
     *
     * @param string $pId Session id
     * @param string $pData Session data
     * @return bool
     */
    public function _write($pId, $pData)
    {
        Agl::validateParams(array(
            'String' => $pData
        ));

        $access = time();

        if (! isset($this->_items[$pId])) {
            $this->_items[$pId] = Agl::getModel(self::DB_COLLECTION);
            $this->_items[$pId]->loadByRealId($pId);
        }

        $this->_items[$pId]
            ->setRealId($pId)
            ->setAccess($access)
            ->setData($pData);

        if (! $this->_items[$pId]->getId()) {
            $this->_items[$pId]->insert();
        } else {
            $this->_items[$pId]->save();
        }

        return true;
    }

    /**
     * Destroy the session from the database.
     *
     * @param string $pId Session id
     * @return bool
     */
    public function _destroy($pId)
    {
        if (! isset($this->_items[$pId])) {
            $this->_items[$pId] = Agl::getModel(self::DB_COLLECTION);
            $this->_items[$pId]->loadByRealId($pId);
        }

        if ($this->_items[$pId]->getId()) {
            $this->_items[$pId]->delete();
        }

        return true;
    }

    /**
     * Clean the database by deleting old sessions.
     *
     * @param int $pMax Session lifetime
     * @return bool
     */
    public function _clean($pMax)
    {
        Agl::validateParams(array(
            'Digit' => $pMax
        ));

        $old = time() - $pMax;

        $collection = new Collection(self::DB_COLLECTION);

        $conditions = new Conditions();
        $conditions->add('access', Conditions::LT, $old);

        $collection->load($conditions);
        $collection->deleteItems();

        return true;
    }
}
