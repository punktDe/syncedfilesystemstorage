# PunktDe.SyncedFileSystemStorage

[![Latest Stable Version](https://poser.pugx.org/punktDe/syncedfilesystemstorage/v/stable)](https://packagist.org/packages/punktDe/syncedfilesystemstorage) [![Total Downloads](https://poser.pugx.org/punktDe/syncedfilesystemstorage/downloads)](https://packagist.org/packages/punktDe/syncedfilesystemstorage) [![License](https://poser.pugx.org/punktDe/syncedfilesystemstorage/license)](https://packagist.org/packages/punktDe/syncedfilesystemstorage)

This package takes care of syncing files, handled by the Flow resource management, to one or several other hosts using rsync. This can be used to build a lightweight multi application server environment without having a central file storage. 

## Prerequisite

* Rsync is installed on all participating servers
* The Neos base path is the same on all servers
* Public key authentication is configured for the user that runs php

## Installation

Install the package using 

    composer require punktde/syncedfilesystemstorage

Configure the target hosts. For the first host just provide the environment variables `RESOURCE_SYNC_TARGET_HOST` and `RESOURCE_SYNC_TARGET_USER`. 
