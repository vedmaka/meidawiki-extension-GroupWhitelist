GroupWhitelist
--------------

The extension allows to grant users from selected group with a special per-page rights
specifying affected pages list on a regular wiki page.


* `wgGroupWhitelistRights` - A list of actions to be allowed
* `wgGroupWhitelistGroup` - A group affected by the extension
* `wgGroupWhitelistSourcePage` - A page to look for list of whitelisted pages

Example config:

```
$wgGroupWhitelistRights = ['edit'];
$wgGroupWhitelistGroup = 'specialusers';
$wgGroupWhitelistSourcePage = 'Project:PageList'; 
```

And the `Project:PageList` contents:

```
SomePage1
SomePage2
SomaPage3
```

The settings above allow users from a `specialusers` group to `edit` pages
specified in the `Project:PageList` page contents (`SomePage1`, `SomePage2`, `SomePage3`).
