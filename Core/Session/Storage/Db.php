<?php
namespace Agl\Core\Session\Storage;

/**
 * Methods to manage session with a database storage.
 *
 * @category Agl_Core
 * @package Agl_Core_Session_Storage
 * @version 0.1.0
 */

class Db
	extends \Agl\Core\Session\SessionAbstract
        implements \Agl\Core\Session\SessionInterface
{
    /**
     * The database collection to store the sessions.
     */
    const DB_COLLECTION = 'sessions';

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
        $item = new \Agl\Core\Db\Item\Item(self::DB_COLLECTION);
        $item->loadById($pId);

        if ($item->getId()) {
            return $item->getData();
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
        \Agl::validateParams(array(
            'StrictString' => $pData
        ));

        $access = time();

        $item = new \Agl\Core\Db\Item\Item(self::DB_COLLECTION);
        $item->loadById($pId);
        $item
            ->setAccess($access)
            ->setData($pData);

        if (! $item->getId()) {
            $item->setId($pId);
        }

        $item->save();

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
        $item = new \Agl\Core\Db\Item\Item(self::DB_COLLECTION);
        $item->loadById($pId);

        if ($item->getId()) {
            $item->delete();
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
        \Agl::validateParams(array(
            'Digit' => $pMax
        ));

        $old = time() - $pMax;

        $collection = new \Agl\Core\Db\Collection\Collection(self::DB_COLLECTION);

        $conditions = new \Agl\Core\Db\Query\Conditions\Conditions();
        $conditions->add('access', $conditions::LT, $old);

        $collection->load($conditions);

        while($item = $collection->getNext()) {
            $item->delete();
        }

        return true;
    }
}
