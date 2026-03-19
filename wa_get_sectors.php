<?php
include 'db_conn.php';

if (isset($_POST['district'])) {
    $district = $_POST['district'];
    
    // Sectors data based on district
    $sectors = [];
    
    switch ($district) {
        // ===== CITY OF KIGALI =====
        case 'Gasabo':
            $sectors = ['Bumbogo', 'Gatsata', 'Gikomero', 'Gisozi', 'Jabana', 'Jali', 'Kacyiru', 'Kimihurura', 'Kimironko', 'Kinyinya', 'Ndera', 'Nduba', 'Remera', 'Rusororo', 'Rutunga'];
            break;
        case 'Kicukiro':
            $sectors = ['Gahanga', 'Gatenga', 'Gikondo', 'Kagarama', 'Kanombe', 'Kicukiro', 'Kigarama', 'Masaka', 'Niboye', 'Nyarugunga'];
            break;
        case 'Nyarugenge':
            $sectors = ['Gitega', 'Kanyinya', 'Kigali', 'Kimisagara', 'Mageragere', 'Muhima', 'Nyakabanda', 'Nyamirambo', 'Nyarugenge', 'Rwezamenyo'];
            break;
            
        // ===== NORTHERN PROVINCE =====
        case 'Burera':
            $sectors = ['Bungwe', 'Butaro', 'Cyanika', 'Cyeru', 'Gahunga', 'Gatebe', 'Gitovu', 'Kagogo', 'Kinoni', 'Kinyababa', 'Kivuye', 'Nemba', 'Rugarama', 'Rugendabari', 'Ruhunde', 'Rusarabuye', 'Rwerere'];
            break;
        case 'Gakenke':
            $sectors = ['Busengo', 'Coko', 'Cyabingo', 'Gakenke', 'Gashenyi', 'Janja', 'Kamubuga', 'Karambo', 'Kivuruga', 'Mataba', 'Minazi', 'Mugunga', 'Muhondo', 'Muyongwe', 'Muzo', 'Nemba', 'Ruli', 'Rusasa', 'Rushashi'];
            break;
        case 'Gicumbi':
            $sectors = ['Bukure', 'Bwisige', 'Byumba', 'Cyumba', 'Giti', 'Kageyo', 'Kaniga', 'Manyagiro', 'Miyove', 'Mukarange', 'Muko', 'Mutete', 'Nyamiyaga', 'Nyankenke', 'Rubaya', 'Rukomo', 'Rushaki', 'Rutare', 'Ruvune', 'Ryamanyoni', 'Shangasha'];
            break;
        case 'Musanze':
            $sectors = ['Busogo', 'Cyuve', 'Gacaca', 'Gashaki', 'Gataraga', 'Kimonyi', 'Kinigi', 'Muhoza', 'Muko', 'Musanze', 'Nkotsi', 'Nyange', 'Remera', 'Rwaza', 'Shingiro'];
            break;
        case 'Rulindo':
            $sectors = ['Base', 'Burega', 'Bushoki', 'Buyoga', 'Cyinzuzi', 'Cyungo', 'Kinihira', 'Kisaro', 'Masoro', 'Mbogo', 'Murambi', 'Ngoma', 'Ntarabana', 'Rukozo', 'Rusiga', 'Shyorongi', 'Tumba'];
            break;
            
        // ===== SOUTHERN PROVINCE =====
        case 'Gisagara':
            $sectors = ['Gikonko', 'Gishubi', 'Kansi', 'Kibirizi', 'Kigembe', 'Mamba', 'Muganza', 'Mugombwa', 'Mukindo', 'Ndora', 'Nyanza', 'Save'];
            break;
        case 'Huye':
            $sectors = ['Gishamvu', 'Huye', 'Karama', 'Kigoma', 'Kinazi', 'Maraba', 'Mbazi', 'Mukura', 'Ngoma', 'Ruhashya', 'Rusatira', 'Simbi', 'Tumba'];
            break;
        case 'Kamonyi':
            $sectors = ['Gacurabwenge', 'Karama', 'Kayenzi', 'Kayumbu', 'Mugina', 'Musambira', 'Ngamba', 'Nyamiyaga', 'Nyarubaka', 'Rugarika', 'Rukoma', 'Runda'];
            break;
        case 'Muhanga':
            $sectors = ['Cyeza', 'Kabacuzi', 'Kibangu', 'Kiyumba', 'Muhanga', 'Mushishiro', 'Nyabinoni', 'Nyamabuye', 'Rongi', 'Rugendabari', 'Shyogwe'];
            break;
        case 'Nyamagabe':
            $sectors = ['Buruhukiro', 'Cyanika', 'Gasaka', 'Gatare', 'Kaduha', 'Kamegeli', 'Kibirizi', 'Kibumbwe', 'Kitabi', 'Mbazi', 'Mugano', 'Musange', 'Musebeya', 'Mushubi', 'Nkomane', 'Nyanza', 'Uwinkingi'];
            break;
        case 'Nyanza':
            $sectors = ['Busasamana', 'Busoro', 'Cyabakamyi', 'Kibirizi', 'Kigoma', 'Mukingo', 'Muyira', 'Ntyazo', 'Nyagisozi', 'Rwabicuma'];
            break;
        case 'Nyaruguru':
            $sectors = ['Busanze', 'Cyahinda', 'Kabiaga', 'Kibeho', 'Kivu', 'Muganza', 'Munini', 'Ngera', 'Ngoma', 'Nyabimata', 'Nyagisozi', 'Ruheru', 'Ruramba', 'Rusenge'];
            break;
        case 'Ruhango':
            $sectors = ['Bweramana', 'Byimana', 'Kabagari', 'Kinazi', 'Mbuye', 'Mwendo', 'Nkozi', 'Ntongwe', 'Ruhango'];
            break;
            
        // ===== EASTERN PROVINCE =====
        case 'Bugesera':
            $sectors = ['Gashora', 'Juru', 'Kamabuye', 'Mareba', 'Mayange', 'Musenyi', 'Mwogo', 'Ngeruka', 'Ntarama', 'Nyamata', 'Nyarugenge', 'Rilima', 'Ruhuha', 'Rweru', 'Shyara'];
            break;
        case 'Gatsibo':
            $sectors = ['Gasange', 'Gatsibo', 'Gitoki', 'Kabarore', 'Kageyo', 'Kiramuruzi', 'Kiziguro', 'Muhura', 'Murambi', 'Ngarama', 'Nyagihanga', 'Remera', 'Rugarama', 'Rwimbogo'];
            break;
        case 'Kayonza':
            $sectors = ['Gahini', 'Kabare', 'Kabarondo', 'Mukarange', 'Murama', 'Murundi', 'Mwiri', 'Ndego', 'Nyamirama', 'Rukara', 'Ruramira', 'Rwinkwavu'];
            break;
        case 'Kirehe':
            $sectors = ['Gahara', 'Gatore', 'Kigarama', 'Kigina', 'Kirehe', 'Mahama', 'Mpanga', 'Musaza', 'Mushikiri', 'Nasho', 'Nyamugari', 'Nyarubuye'];
            break;
        case 'Ngoma':
            $sectors = ['Gashanda', 'Jarama', 'Karembo', 'Kazo', 'Kibungo', 'Mugesera', 'Murama', 'Mutenderi', 'Remera', 'Rukira', 'Rukumberi', 'Rurenge', 'Sake', 'Zaza'];
            break;
        case 'Nyagatare':
            $sectors = ['Gatunda', 'Karama', 'Karangazi', 'Katabagemu', 'Kiyombe', 'Matimba', 'Mimuli', 'Mukama', 'Musheli', 'Nyagatare', 'Rukomo', 'Rwempasha', 'Rwimiyaga', 'Tabagwe'];
            break;
        case 'Rwamagana':
            $sectors = ['Fumbwe', 'Gahengeri', 'Gishari', 'Karenge', 'Kigabiro', 'Muhazi', 'Munyaga', 'Munyiginya', 'Musha', 'Muyumbu', 'Mwulire', 'Nyakariro', 'Nzige', 'Rubona'];
            break;
            
        // ===== WESTERN PROVINCE =====
        case 'Karongi':
            $sectors = ['Bisesero', 'Bwishyura', 'Gishyita', 'Gitesi', 'Mubuga', 'Murambi', 'Murundi', 'Mutuntu', 'Rubengera', 'Rugabano', 'Ruganda', 'Rwankuba', 'Twumba'];
            break;
        case 'Ngororero':
            $sectors = ['Bwira', 'Gatumba', 'Kabaya', 'Kageyo', 'Kavumu', 'Kinyababa', 'Muhanda', 'Muhororo', 'Ndaro', 'Ngororero', 'Nyange', 'Sovu'];
            break;
        case 'Nyabihu':
            $sectors = ['Bigogwe', 'Jenda', 'Jomba', 'Kabatwa', 'Karago', 'Kintobo', 'Mukamira', 'Muringa', 'Rambura', 'Rugera', 'Rurembo', 'Shyira'];
            break;
        case 'Nyamasheke':
            $sectors = ['Bushekeri', 'Bushenge', 'Cyato', 'Gihombo', 'Kagano', 'Kanjongo', 'Karambi', 'Karengera', 'Kirimbi', 'Macuba', 'Mahembe', 'Nyabitekeri', 'Rangiro', 'Ruharambuga', 'Shangi'];
            break;
        case 'Rubavu':
            $sectors = ['Bugeshi', 'Gisenyi', 'Kanama', 'Kanzenze', 'Mudende', 'Nyakiliba', 'Nyamyumba', 'Nyundo', 'Rubavu', 'Rugerero', 'Rwerere'];
            break;
        case 'Rusizi':
            $sectors = ['Bugarama', 'Butare', 'Bweyeye', 'Gashonga', 'Giheke', 'Gihundwe', 'Gikundamvura', 'Gilinda', 'Gitega', 'Kamembe', 'Muganza', 'Mururu', 'Nkanka', 'Nkombo', 'Nyakabuye', 'Nyakarenzo'];
            break;
        case 'Rutsiro':
            $sectors = ['Boneza', 'Gihango', 'Kigeyo', 'Kivumu', 'Manihira', 'Mukura', 'Murunda', 'Musasa', 'Mushonyi', 'Musigati', 'Nyabirasi', 'Ruhango', 'Rusebeya'];
            break;
            
        default:
            // Try to fetch from database as fallback
            $district_escaped = mysqli_real_escape_string($conn, $district);
            $sql = "SELECT DISTINCT sector FROM rw_location WHERE district='$district_escaped' ORDER BY sector ASC";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $sectors[] = $row['sector'];
                }
            }
            break;
    }
    
    // Sort sectors alphabetically
    sort($sectors);
    
    // Output options
    $output = '';
    foreach ($sectors as $sector) {
        $output .= '<option value="' . htmlspecialchars($sector) . '">' . htmlspecialchars($sector) . '</option>';
    }
    
    echo $output;
}
?>