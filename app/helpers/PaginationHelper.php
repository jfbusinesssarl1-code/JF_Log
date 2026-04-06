<?php
namespace App\Helpers;

class PaginationHelper
{
    private $currentPage;
    private $itemsPerPage;
    private $totalItems;
    private $totalPages;

    public function __construct($currentPage = 1, $itemsPerPage = 20)
    {
        $this->currentPage = max(1, (int) $currentPage);
        $this->itemsPerPage = max(1, (int) $itemsPerPage);
        $this->totalItems = 0;
        $this->totalPages = 0;
    }

    /**
     * Définir le nombre total d'éléments
     */
    public function setTotalItems($total)
    {
        $this->totalItems = max(0, (int) $total);
        $this->totalPages = ceil($this->totalItems / $this->itemsPerPage);
        // Ajuster la page actuelle si elle dépasse le nombre de pages
        if ($this->currentPage > $this->totalPages && $this->totalPages > 0) {
            $this->currentPage = $this->totalPages;
        }
    }

    /**
     * Obtenir l'offset pour la requête MongoDB
     */
    public function getOffset()
    {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }

    /**
     * Obtenir le nombre d'éléments par page
     */
    public function getLimit()
    {
        return $this->itemsPerPage;
    }

    /**
     * Obtenir la page actuelle
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Obtenir le nombre total de pages
     */
    public function getTotalPages()
    {
        return $this->totalPages;
    }

    /**
     * Obtenir le nombre total d'éléments
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }

    /**
     * Vérifier s'il y a une page précédente
     */
    public function hasPreviousPage()
    {
        return $this->currentPage > 1;
    }

    /**
     * Vérifier s'il y a une page suivante
     */
    public function hasNextPage()
    {
        return $this->currentPage < $this->totalPages;
    }

    /**
     * Obtenir le numéro de la page précédente
     */
    public function getPreviousPage()
    {
        return max(1, $this->currentPage - 1);
    }

    /**
     * Obtenir le numéro de la page suivante
     */
    public function getNextPage()
    {
        return min($this->totalPages, $this->currentPage + 1);
    }

    /**
     * Obtenir un tableau avec les numéros de pages pour affichage
     * Affiche les pages à proximité de la page actuelle
     */
    public function getPageNumbers($range = 2)
    {
        $pages = [];
        $start = max(1, $this->currentPage - $range);
        $end = min($this->totalPages, $this->currentPage + $range);

        if ($start > 1) {
            $pages[] = 1;
            if ($start > 2) {
                $pages[] = '...';
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        if ($end < $this->totalPages) {
            if ($end < $this->totalPages - 1) {
                $pages[] = '...';
            }
            $pages[] = $this->totalPages;
        }

        return $pages;
    }

    /**
     * Obtenir le message de pagination (ex: "Affichage 1-20 sur 150")
     */
    public function getDisplayMessage()
    {
        if ($this->totalItems === 0) {
            return "Aucun résultat";
        }
        $start = $this->getOffset() + 1;
        $end = min($this->getOffset() + $this->itemsPerPage, $this->totalItems);
        return "Vue de $start-$end sur {$this->totalItems}";
    }
}