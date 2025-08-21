<div class="border-b border-gray-200 mb-4">
  <ul class="flex text-sm font-medium text-center text-gray-500">
    <li class="me-2">
      <a href="?tab=materials" class="inline-block p-4 rounded-t-lg 
        <?= ($_GET['tab'] ?? 'materials') == 'materials' ? 'text-blue-600 border-b-2 border-blue-600' : '' ?>">
        📚 Materials
      </a>
    </li>
    <li class="me-2">
      <a href="?tab=assignments" class="inline-block p-4 rounded-t-lg 
        <?= ($_GET['tab'] ?? '') == 'assignments' ? 'text-blue-600 border-b-2 border-blue-600' : '' ?>">
        📝 Assignments
      </a>
    </li>
    <li class="me-2">
      <a href="?tab=discussion" class="inline-block p-4 rounded-t-lg 
        <?= ($_GET['tab'] ?? '') == 'discussion' ? 'text-blue-600 border-b-2 border-blue-600' : '' ?>">
        💬 Discussion
      </a>
    </li>
  </ul>
</div>

<div class="bg-white p-4 rounded shadow-sm">
  <?php
    $tab = $_GET['tab'] ?? 'materials';

    switch ($tab) {
      case 'materials':
        include 'tabs/materials_tab.php';
        break;
      case 'assignments':
        include 'tabs/assignments_tab.php';
        break;
      case 'discussion':
        include 'tabs/discussion_tab.php';
        break;
      default:
        echo "<p class='text-gray-500'>Invalid tab.</p>";
    }
  ?>
</div>