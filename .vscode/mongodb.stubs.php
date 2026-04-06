<?php
/**
 * MongoDB PHP Driver Stubs for IDE autocompletion
 * This file helps the IDE recognize MongoDB classes
 */

namespace MongoDB\BSON {
    /**
     * BSON ObjectId class stub
     * @link https://www.php.net/manual/en/class.mongodb-bson-objectid.php
     */
    class ObjectId {
        /**
         * Create a new ObjectId
         * @param string|null $id
         */
        public function __construct(?string $id = null) {}
        
        public function __toString() { return ''; }
    }

    /**
     * BSON UTCDateTime class stub
     * @link https://www.php.net/manual/en/class.mongodb-bson-utcdatetime.php
     */
    class UTCDateTime {
        /**
         * Create a new UTCDateTime
         * @param int|float|null $milliseconds
         */
        public function __construct(int|float|null $milliseconds = null) {}
        
        public function __toString() { return ''; }
    }

    /**
     * BSON Regex class stub
     * @link https://www.php.net/manual/en/class.mongodb-bson-regex.php
     */
    class Regex {
        /**
         * Create a new Regex
         * @param string $pattern
         * @param string $flags
         */
        public function __construct(string $pattern, string $flags = '') {}
        
        public function __toString() { return ''; }
    }
    
    /**
     * BSON Binary class stub
     */
    class Binary {
        /**
         * Create a new Binary
         * @param string $data
         * @param int $type
         */
        public function __construct(string $data, int $type = 0) {}
        
        public function __toString() { return ''; }
    }
    
    /**
     * BSON Timestamp class stub
     */
    class Timestamp {
        /**
         * Create a new Timestamp
         * @param int $increment
         * @param int $timestamp
         */
        public function __construct(int $increment, int $timestamp) {}
        
        public function __toString() { return ''; }
    }
}

namespace MongoDB\Driver {
    /**
     * Manager class stub
     * @link https://www.php.net/manual/en/class.mongodb-driver-manager.php
     */
    class Manager {
        /**
         * Create a new Manager
         * @param string $dsn
         * @param array $options
         */
        public function __construct(string $dsn, array $options = []) {}
    }

    /**
     * Query class stub
     */
    class Query {
        /**
         * Create a new Query
         * @param object|array $filter
         * @param array $options
         */
        public function __construct(object|array $filter, array $options = []) {}
    }
    
    /**
     * WriteResult class stub
     */
    class WriteResult {
        public function getDeletedCount() { return 0; }
        public function getInsertedCount() { return 0; }
        public function getModifiedCount() { return 0; }
    }
}


