How to clone Content blocks per domain:
-------
 1. For begin testing process you should have as minimum 2 domains available on your site
 2. Enable "Domain custom block" module

 3. Go to "Domain entity" /admin/config/domain/entities page
 4. Check "Custom block" checkbox and click "Save configuration" button
 5. For "Custom block" line appears "Configure" operation button, click it
 6. For all "Custom block" type that should be clone-able per domain:
 6.1. Check "Enable domain entity access" checkbox
 6.2. In behavior select should be selected: "User choose affiliate ..."
 6.3. Click "Save configuration" button

 7. Go to "Custom block library : Block types" /admin/structure/block/block-content/types page
 8. Click all block types (that should clone-able per domain) "Edit" link (in operations list).
 9. Check "Allow add duplicates for specific domain" checkbox on block type edit page and click "Save" button.
 9. Click all block types (that should clone-able per domain) "Manage fields" link (in operations list).
 9.1. All this block types should have "Domain Access" field. If no - return to step #5.
 9.2. All this block types should have "Domain block parent" field. If no - return to step #8.

Interaction with new block:
-----
 10. Go to "Custom block library" /admin/structure/block/block-content page
 11. Click "+ Add custom block" button
 12. On "Add custom block" page:
 12.1. Fill form with values (set block title: "Main test block")
 12.2. On "Domain Access" section choose 1 domain only
 12.3. Click "Save" button
 13. Open just added block operations list and click "Clone"
 14. On "Clone Custom block" confirmation page click "Clone" button
 15. After successful block cloning you will land to cloned block edit page:
 15.1. Enter different block title and content (set block title: "Clone of Main test block")
 15.2. On "Domain Access" section choose 1 domain
 15.3. Click "Save" button

Core block UI:
---
 16. Go to "Block layout" /admin/structure/block page
 17. Click "Place block" on desired section
 18. In blocks list:
 18.1. Find "Main test block" (block that was added first, in section above)
 18.2. Click "Place block" button (in line with "Main test block")
 18.3. Setup block visibility
 18.4. Click "Save block"
 19. Visit site page where should be available block and check: Related content block displayed.
 20. Visit site sub-domain page where should be available block and check: Related content block displayed.

Panels UI:
---
 16. Go to some already existing panel pane content page
 17. Click "+ Add new block" button
 18. In blocks list:
 18.1. Find "Main test block" (block that was added first, in section above)
 18.2. Click by it
 18.3. Setup block settings
 18.4. Click "Add block" button
 19. Click "Update and save" button
 20. Visit site (related to edited pane page): Related content block displayed.
 21. Visit site sub-domain (related to edited pane page): Related content block displayed.

Interaction with existing block:
-----
 10. Go to "Custom block library" /admin/structure/block/block-content page
 11. Click "Edit" on some existing block (that should be cloned)
 12. On "Domain Access" section choose 1 domain only
 13. Click "Save" button
 14. Open just edited block operations list and click "Clone"
 15. On "Clone Custom block" confirmation page click "Clone" button
 16. After successful block cloning you will land to cloned block edit page:
 16.1. Enter different block title and content (set block title: "Clone of Main test block")
 16.2. On "Domain Access" section choose 1 domain
 16.3. Click "Save" button
 17. Visit site page where should be available block and check: Related content block displayed.
 18. Visit site sub-domain page where should be available block and check: Related content block displayed.