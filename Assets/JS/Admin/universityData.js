/**
 * Sri Lankan Universities Dataset
 * Official list of state universities recognized by the UGC Sri Lanka,
 * along with major higher education institutes.
 */
const sriLankaUniversities = [
    {
        name: "University of Colombo",
        domain: "cmb.ac.lk",
        location: "Colombo 07"
    },
    {
        name: "University of Peradeniya",
        domain: "pdn.ac.lk",
        location: "Peradeniya, Kandy"
    },
    {
        name: "University of Sri Jayewardenepura",
        domain: "sjp.ac.lk",
        location: "Nugegoda"
    },
    {
        name: "University of Kelaniya",
        domain: "kln.ac.lk",
        location: "Kelaniya"
    },
    {
        name: "University of Moratuwa",
        domain: "mrt.ac.lk",
        location: "Katubedda, Moratuwa"
    },
    {
        name: "University of Jaffna",
        domain: "jfn.ac.lk",
        location: "Thirunelvely, Jaffna"
    },
    {
        name: "University of Ruhuna",
        domain: "ruh.ac.lk",
        location: "Matara"
    },
    {
        name: "The Open University of Sri Lanka",
        domain: "ou.ac.lk",
        location: "Nawala, Nugegoda"
    },
    {
        name: "Eastern University, Sri Lanka",
        domain: "esn.ac.lk",
        location: "Vantharumoolai, Chenkalady"
    },
    {
        name: "South Eastern University of Sri Lanka",
        domain: "seu.ac.lk",
        location: "Oluvil"
    },
    {
        name: "Rajarata University of Sri Lanka",
        domain: "rjt.ac.lk",
        location: "Mihintale"
    },
    {
        name: "Sabaragamuwa University of Sri Lanka",
        domain: "sab.ac.lk",
        location: "Belihuloya"
    },
    {
        name: "Wayamba University of Sri Lanka",
        domain: "wyb.ac.lk",
        location: "Kuliyapitiya"
    },
    {
        name: "Uva Wellassa University of Sri Lanka",
        domain: "uwu.ac.lk",
        location: "Badulla"
    },
    {
        name: "University of the Visual & Performing Arts",
        domain: "vpa.ac.lk",
        location: "Colombo 07"
    },
    {
        name: "Gampaha Wickramarachchi University of Indigenous Medicine",
        domain: "gwu.ac.lk",
        location: "Yakkala"
    },
    {
        name: "University of Vavuniya",
        domain: "vau.ac.lk",
        location: "Vavuniya"
    },
    {
        name: "SLIIT",
        domain: "sliit.lk",
        location: "Malabe"
    },
    {
        name: "General Sir John Kotelawala Defence University (KDU)",
        domain: "kdu.ac.lk",
        location: "Ratmalana"
    },
    {
        name: "NSBM Green University",
        domain: "nsbm.ac.lk",
        location: "Homagama"
    },
    {
        name: "Ocean University of Sri Lanka",
        domain: "ocu.ac.lk",
        location: "Crow Island, Colombo"
    },
    {
        name: "Informatics Institute of Technology (IIT)",
        domain: "iit.ac.lk",
        location: "Colombo 06"
    },
    {
        name: "CINEC Campus",
        domain: "cinec.edu",
        location: "Malabe"
    },
    {
        name: "Horizon Campus",
        domain: "horizoncampus.edu.lk",
        location: "Malabe"
    },
    {
        name: "Sri Lanka Technological Campus (SLTC)",
        domain: "sltc.ac.lk",
        location: "Padukka"
    },
    {
        name: "National Institute of Business Management (NIBM)",
        domain: "nibm.lk",
        location: "Colombo 07"
    }
];

/**
 * Asynchronously fetch / retrieve the list of Sri Lankan universities.
 * Returns a Promise that resolves with the list.
 */
function fetchSriLankanUniversities() {
    return new Promise((resolve) => {
        // Simulating async fetch (e.g. from an API or local data)
        setTimeout(() => {
            resolve(sriLankaUniversities);
        }, 30);
    });
}
