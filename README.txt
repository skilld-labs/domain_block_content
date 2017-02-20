How to clone Content blocks per domain:
 - For begin testing process you should have as minimum 2 domains available on your site
 - Enable "Domain custom block" module
 - Go to "Domain entity" /admin/config/domain/entities page
 - Check "Custom block" checkbox and click "Save configuration" button
 - For "Custom block" line appears "Configure" operation button, click it
 - For all "Custom block" type that should be clone-able per domain:
 -- Check "Enable domain entity access" checkbox
 -- In behavior select should be selected: "User choose affiliate ..."
 -- Click "Save configuration" button
 - Go to "Custom block library" /admin/structure/block/block-content page
 - Click "+ Add custom block" button
 - On "Add custom block" page:
 -- Fill form with values (set block title: "Main test block")
 -- On "Domain Access" section choose 1 domain only
 -- Click "Save" button
 - Open just added block operations list and click "Clone"
 - On "Clone Custom block" confirmation page click "Clone" button
 - After successful block cloning you will land to cloned block edit page:
 -- Enter different block title and content (set block title: "Clone of Main test block")
 -- On "Domain Access" section choose 1 domain
 -- Click "Save" button

Core block UI:
 - Go to "Block layout" /admin/structure/block page
 - Click "Place block" on desired section
 - In blocks list:
 -- Find "Main test block" (block that was added first, in section above)
 -- Click "Place block" button (in line with "Main test block")
 -- Setup block visibility
 -- Click "Save block"
 - Visit site page where should be available block and check: Related content block displayed.
 - Visit site sub-domain page where should be available block and check: Related content block displayed.

Panels UI:
 - Go to some already existing panel pane content page
 - Click "+ Add new block" button
 - In blocks list:
 -- Find "Main test block" (block that was added first, in section above)
 -- Click by it
 -- Setup block settings
 -- Click "Add block" button
 - Click "Update and save" button
 - Visit site (related to edited pane page): Related content block displayed.
 - Visit site sub-domain (related to edited pane page): Related content block displayed.
