<?php
namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Ldap\Entry;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FachRepository;
use App\Entity\Fach;
use App\Entity\Klasse;
use App\Repository\KlasseRepository;
use App\Repository\SchuelerRepository;
use App\Entity\Schueler;
use App\Repository\LehrerRepository;
use App\Entity\Lehrer;

class LdapImportService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly LdapInterface $ldap,
        private readonly string $searchDn,
        private readonly string $searchPassword,
        private readonly string $lehrerDn,
        private readonly string $studentsDn,
        private readonly string $loginAttr,
        private readonly EntityManagerInterface $em,
        private readonly FachRepository $fachRepo,
        private readonly KlasseRepository $klasseRepo,
        private readonly SchuelerRepository $schuelerRepo,
        private readonly LehrerRepository $lehrerRepo,
    ){
        try {
            $this->ldap->bind($this->searchDn, $this->searchPassword);
        } catch (ConnectionException $e) {
            throw new CustomUserMessageAuthenticationException('Verbindung zum LDAP-Server fehlgeschlagen.', [], 0, $e);
        }
    }
    
    public function searchGroupOfNames(string $dn): array
    {
        $data = [];
        $filter = sprintf('(objectClass=groupOfNames)');
        $query = $this->ldap->query($dn, $filter);
        $results = $query->execute()->toArray();
        /** @var Entry $entry */
        foreach ($results AS $entry) 
        {
            $data[$entry->getDn()] =  $entry->getAttribute('cn');
        }
        return $data;
    }
    
    
    public function searchInetOrgPerson(string $dn): array
    {
        $data = [];
        $filter = sprintf('(objectClass=inetOrgPerson)');
        $query = $this->ldap->query($dn, $filter);
        $results = $query->execute()->toArray();
        /** Entry $entry */
        foreach ($results AS $entry)
        {
            $data[$entry->getDn()] =  $entry->getAttribute('cn');
        }
        return $data;
    }
    
    
    public function searchOrganizationalPerson(string $dn): array
    {
        $data = [];
        $filter = sprintf('(objectClass=organizationalPerson)');
        $query = $this->ldap->query($dn, $filter);
        $results = $query->execute()->toArray();
        /** Entry $entry */
        foreach ($results AS $entry)
        {
            $data[$entry->getDn()] =  $entry->getAttribute('cn');
        }
        return $data;
    }
    
    public function importFaecher(string $fachDn): array 
    {
        $data=[];
        foreach ($this->searchGroupOfNames($fachDn) AS $dn=>$cn) {
            /** @var ?Fach $fach */
            $fach = $this->fachRepo->findOneByDn($dn);
            if(!$fach) {
                $fach = new Fach();
                $fach->setDn($dn);
                $fach->setLabel($cn[0]);
                $fach->setDn($dn);
                $fach->setVisible(false);
                $this->em->persist($fach);
                $data[] = $fach;
            }
        }
        if(sizeof($data)>0) {
            $this->em->flush();
        }
        return $data;
    }
    
    public function importKlassen(string $klassenDn): array
    {
        $data=[];
        foreach ($this->searchGroupOfNames($klassenDn) AS $dn=>$cn){
            /** @var Klasse $klasse */
            $klasse = $this->klasseRepo->findOneByDn($dn);
            if(!$klasse) {
                $klasse = new Klasse();
                $klasse->setDn($dn);
                $klasse->setLabel($cn[0]);
                $klasse->setVisible(false);
                $this->em->persist($klasse);
                $data[] = $klasse;
            }
        }
        if(sizeof($data)>0) {
            $this->em->flush();
        }
        return $data;
    }
    
    /**
     * 
     * @param string $klassenCn
     * @return array
     */
    public function importLehrerFromKlass(string $klassenDn): array
    {
        $data = [];
        $parts=ldap_explode_dn($klassenDn, 0);
        $klassenCn=$parts[0];
        $search = $this->ldap->query($this->studentsDn, $klassenCn);
        $entries = $search->execute();
        if(sizeof($entries)===1) {
            /** @var Entry $entry */
            $entry = $entries[0];
        }
        $changes=[];
        foreach ($entry->getAttribute('member') AS $memberDn) {
            $memberParts=ldap_explode_dn($memberDn, 0);
            $memberCn=$memberParts[0];
            $lehrerSearch = $this->ldap->query($this->lehrerDn, $memberCn);
            $lehrerEntries = $lehrerSearch->execute();
            if(sizeof($lehrerEntries)===1) {
                /** @var Entry $lehrerEntry */
                $lehrerEntry = $lehrerEntries[0];
                $username = $lehrerEntry->getAttribute($this->loginAttr)[0];
                $lehrer = $this->lehrerRepo->findByLdapUsername($username);
                if(!$lehrer) {
                    $lehrer = new Lehrer();
                    $lehrer->setLdapUsername($username);
                    $this->em->persist($lehrer);
                    $changes[]=$lehrer;
                }
                if($lehrer->getDn() !==$lehrerEntry->getDn()) {
                    $lehrer->setDn($lehrerEntry->getDn());
                    $changes[]=$lehrer;
                }
                $data[] = $lehrer;
            }
        }
        if(sizeof(array_unique($changes))>0) {
            $this->em->flush();
        }
        return $data;
    }
    
    public function importSchueler(string $schuelerCn): Schueler
    {
        $filter = sprintf('(objectClass=inetOrgPerson)');
        $query = $this->ldap->query($schuelerCn, $filter);
        $results = $query->execute();
        if(sizeof($results)==1) {
            /** @var Entry $entry */
            $entry = $results[0];
            $entity = $this->schuelerRepo->findOneByDn($entry->getDn());
            if(!$entity) {
                $entity = new Schueler();
                $entity->setDn($entry->getDn());
                $entity->setVisible(true);
                $entity->setLabel($entry->getAttribute('displayName')[0]);
                $this->em->persist($entity);
                $this->em->flush();
            }
        }
        return $entity;
    }
}