
//This code works fine but delete function is not 

// import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.4/firebase-app.js";
// import {
//     getDatabase,
//     ref,
//     set,
//     get,
//     onValue,
//     goOffline,
//     goOnline
// } from "https://www.gstatic.com/firebasejs/10.12.4/firebase-database.js";

// // Firebase Configuration
// const firebaseConfig = {
//     apiKey: "<?php echo getenv('FIREBASE_API_KEY'); ?>",
//     authDomain: "<?php echo getenv('FIREBASE_AUTH_DOMAIN'); ?>",
//     databaseURL: "https://graderadmin-default-rtdb.firebaseio.com",
//     projectId: "<?php echo getenv('FIREBASE_PROJECT_ID'); ?>",
//     storageBucket: "<?php echo getenv('FIREBASE_STORAGE_BUCKET'); ?>",
//     messagingSenderId: "<?php echo getenv('FIREBASE_MESSAGING_SENDER_ID'); ?>",
//     appId: "<?php echo getenv('FIREBASE_APP_ID'); ?>"
// };

// // Initialize Firebase
// const app = initializeApp(firebaseConfig);
// const database = getDatabase(app);

// // IndexedDB configuration
// const dbName = 'schoolDatabase';
// const dbVersion = 1;
// let db;

// // IndexedDB Utility Functions
// export async function openDb() {
//     return new Promise((resolve, reject) => {
//         const request = indexedDB.open(dbName, dbVersion);
//         request.onupgradeneeded = (event) => {
//             db = event.target.result;
//             db.createObjectStore('schoolData', { keyPath: 'schoolId' });
//         };
//         request.onsuccess = (event) => {
//             db = event.target.result;
//             resolve(db);
//         };
//         request.onerror = (event) => reject(`Error opening IndexedDB: ${event.target.errorCode}`);
//     });
// }

// export async function storeData(storeName, key, data) {
//     const db = await openDb();
//     return new Promise((resolve, reject) => {
//         const transaction = db.transaction(storeName, 'readwrite');
//         const store = transaction.objectStore(storeName);
//         const dataWithKey = { schoolId: key, ...data };
//         const request = store.put(dataWithKey);

//         request.onsuccess = () => resolve();
//         request.onerror = (event) => reject(`Error storing data: ${event.target.errorCode}`);
//     });
// }

// export async function getData(storeName, key) {
//     const db = await openDb();
//     return new Promise((resolve, reject) => {
//         const transaction = db.transaction(storeName, 'readonly');
//         const store = transaction.objectStore(storeName);
//         const request = store.get(key);

//         request.onsuccess = (event) => resolve(event.target.result ? event.target.result : null);
//         request.onerror = (event) => reject(`Error retrieving data: ${event.target.errorCode}`);
//     });
// }

// export async function clearIndexedDB() {
//     return new Promise((resolve, reject) => {
//         const request = indexedDB.deleteDatabase(dbName);
//         request.onsuccess = () => resolve('IndexedDB cleared successfully.');
//         request.onerror = (event) => reject(`Error clearing IndexedDB: ${event.target.errorCode}`);
//     });
// }

// // Data Handling Functions
// export async function fetchSchoolName(schoolId) {
//     try {
//         const schoolNameRef = ref(database, `School_ids/${schoolId}`);
//         const schoolNameSnapshot = await get(schoolNameRef);
//         if (schoolNameSnapshot.exists()) {
//             return schoolNameSnapshot.val();
//         } else {
//             console.warn(`School name not found for school ID: ${schoolId}`);
//             return null;
//         }
//     } catch (error) {
//         console.error(`Error fetching school name for school ID: ${schoolId}`, error);
//         return null;
//     }
// }

// export async function fetchAndCacheAllData(schoolId) {
//     try {
//         const schoolName = await fetchSchoolName(schoolId);
//         if (!schoolName) return;

//         const paths = [
//             `School_ids/${schoolId}`,
//             `Users/Parents/${schoolId}/`,
//             `Users/Teachers/${schoolId}/`,
//             `Schools/${schoolName}/`,
//             `Users/Schools/${schoolName}/`,
//             `School_ids/Count`,
//             `Users/Parents/Count`,
//             `Users/Teachers/Count`,
//             'Exits/',
//             'User_ids_pno/'
//         ];

//         const allData = {};

//         for (const path of paths) {
//             const dataRef = ref(database, path);
//             try {
//                 const snapshot = await get(dataRef);
//                 if (snapshot.exists()) {
//                     allData[path] = snapshot.val();
//                 }
//             } catch (error) {
//                 console.error(`Error fetching data from path: ${path}`, error);
//             }
//         }

//         // Filter Exits data based on schoolId
//         if (allData['Exits/']) {
//             allData['Exits/'] = Object.fromEntries(
//                 Object.entries(allData['Exits/']).filter(([key, value]) => value === String(schoolId))
//             );
//         }

//         // Filter User_ids_pno based on filtered Exits phone numbers
//         if (allData['User_ids_pno/'] && allData['Exits/']) {
//             const validPhoneNumbers = Object.keys(allData['Exits/']);
//             allData['User_ids_pno/'] = Object.fromEntries(
//                 Object.entries(allData['User_ids_pno/']).filter(([key]) => validPhoneNumbers.includes(key))
//             );
//         }

//         // Cache the fetched data locally using IndexedDB
//         await storeData('schoolData', schoolId, allData);
//         console.log("Filtered school data cached locally:", allData);
//     } catch (error) {
//         console.error("Error fetching and caching all data:", error);
//     }
// }

// export async function getCachedData(schoolId) {
//     try {
//         const cachedData = await getData('schoolData', schoolId);
//         //console.log(cachedData);
//         return cachedData ? cachedData : null;
//     } catch (error) {
//         console.error("Error getting cached data:", error);
//         return null;
//     }
// }




// export async function listenForUpdates(schoolId) {
//     try {
//         const schoolName = await fetchSchoolName(schoolId);
//         if (!schoolName) return;

//         const paths = [
//             `School_ids/${schoolId}`,
//             `Users/Parents/${schoolId}/`,
//             `Users/Teachers/${schoolId}/`,
//             `Schools/${schoolName}/`,
//             `Users/Schools/${schoolName}/`,
//             `School_ids/Count`,
//             `Users/Parents/Count`,
//             `Users/Teachers/Count`,
//             'Exits/',
//             'User_ids_pno/'
//         ];

//         paths.forEach(path => {
//             const dataRef = ref(database, path);
//             onValue(dataRef, async (snapshot) => {
//                 if (snapshot.exists()) {
//                     const data = snapshot.val();
//                     const cachedData = await getCachedData(schoolId) || {};
//                     cachedData[path] = data;

//                     // Filter the data for the Exits and User_ids_pno paths
//                     if (path === 'Exits/') {
//                         cachedData[path] = Object.fromEntries(
//                             Object.entries(data).filter(([key, value]) => value === String(schoolId))
//                         );
//                     } else if (path === 'User_ids_pno/' && cachedData['Exits/']) {
//                         const validPhoneNumbers = Object.keys(cachedData['Exits/']);
//                         cachedData[path] = Object.fromEntries(
//                             Object.entries(data).filter(([key]) => validPhoneNumbers.includes(key))
//                         );
//                     }

//                     await storeData('schoolData', schoolId, cachedData);
//                    // console.log(`Data updated locally for ${path}:`, cachedData[path]);
//                 }
//             });
//         });
//     } catch (error) {
//         console.error("Error listening for updates:", error);
//     }
// }

// // Data Synchronization Function
// export async function syncDataWithFirebase(schoolId) {
//     try {
//         const cachedData = await getCachedData(schoolId);

//         if (cachedData) {
//             // Extract data for all paths that need synchronization
//             const paths = [
//                 `School_ids/${schoolId}`,
//                 `Users/Parents/${schoolId}/`,
//                 `Users/Teachers/${schoolId}/`,
//                 `Schools/${cachedData['School_ids']}`,
//                 `Users/Schools/${cachedData['School_ids']}`,
//                 'Exits/',
//                 'User_ids_pno/'
//             ];

//             // Iterate through paths to synchronize each one
//             for (const path of paths) {
//                 const dataRef = ref(database, path);
//                 if (cachedData[path]) {
//                     // Update Firebase with data from IndexedDB
//                     await set(dataRef, cachedData[path]);
//                     console.log(`Data synced to Firebase at path: ${path}`);
//                 }
//             }
//         } else {
//             console.log("No cached data to sync.");
//         }
//     } catch (error) {
//         console.error("Error syncing data with Firebase:", error);
//     }
// }

// // Function to delete data from both Firebase and IndexedDB
// export async function deleteData(schoolId, path) {
//     // Delete data from Firebase
//     await deleteFromFirebase(path);
    
//     // Delete data from IndexedDB
//     await deleteFromIndexedDB(schoolId);
// }

// async function deleteFromFirebase(path) {
//     // Your logic to delete data from Firebase using the path
//     const dbRef = firebase.database().ref(path);
//     await dbRef.remove();
// }

// async function deleteFromIndexedDB(schoolId) {
//     // Your logic to delete data from IndexedDB
//     // Assuming you have a store named 'schools'
//     return new Promise((resolve, reject) => {
//         const request = indexedDB.open('SchoolDB', 1); // Adjust the DB name as necessary
//         request.onerror = (event) => reject(event.target.error);
//         request.onsuccess = (event) => {
//             const db = event.target.result;
//             const transaction = db.transaction('schools', 'readwrite');
//             const store = transaction.objectStore('schools');
//             store.delete(schoolId);
//             transaction.oncomplete = () => resolve();
//             transaction.onerror = (event) => reject(event.target.error);
//         };
//     });
// }



import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.4/firebase-app.js";
import {
    getDatabase,
    ref,
    set,
    get,
    remove,
    onValue,
    goOffline,
    goOnline
} from "https://www.gstatic.com/firebasejs/10.12.4/firebase-database.js";

// Firebase Configuration
const firebaseConfig = {
    apiKey: "<?php echo getenv('FIREBASE_API_KEY'); ?>",
    authDomain: "<?php echo getenv('FIREBASE_AUTH_DOMAIN'); ?>",
    databaseURL: "https://graderadmin-default-rtdb.firebaseio.com",
    projectId: "<?php echo getenv('FIREBASE_PROJECT_ID'); ?>",
    storageBucket: "<?php echo getenv('FIREBASE_STORAGE_BUCKET'); ?>",
    messagingSenderId: "<?php echo getenv('FIREBASE_MESSAGING_SENDER_ID'); ?>",
    appId: "<?php echo getenv('FIREBASE_APP_ID'); ?>"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const database = getDatabase(app);

// IndexedDB configuration
const dbName = 'schoolDatabase';
const dbVersion = 1;
let db;

// IndexedDB Utility Functions
export async function openDb() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(dbName, dbVersion);
        request.onupgradeneeded = (event) => {
            db = event.target.result;
            db.createObjectStore('schoolData', { keyPath: 'schoolId' });
        };
        request.onsuccess = (event) => {
            db = event.target.result;
            resolve(db);
        };
        request.onerror = (event) => reject(`Error opening IndexedDB: ${event.target.errorCode}`);
    });
}

export async function storeData(storeName, key, data) {
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(storeName, 'readwrite');
        const store = transaction.objectStore(storeName);
        const dataWithKey = { schoolId: key, ...data };
        const request = store.put(dataWithKey);

        request.onsuccess = () => resolve();
        request.onerror = (event) => reject(`Error storing data: ${event.target.errorCode}`);
    });
}

export async function getData(storeName, key) {
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(storeName, 'readonly');
        const store = transaction.objectStore(storeName);
        const request = store.get(key);

        request.onsuccess = (event) => resolve(event.target.result ? event.target.result : null);
        request.onerror = (event) => reject(`Error retrieving data: ${event.target.errorCode}`);
    });
}

export async function clearIndexedDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.deleteDatabase(dbName);
        request.onsuccess = () => resolve('IndexedDB cleared successfully.');
        request.onerror = (event) => reject(`Error clearing IndexedDB: ${event.target.errorCode}`);
    });
}

// Data Handling Functions
export async function fetchSchoolName(schoolId) {
    try {
        const schoolNameRef = ref(database, `School_ids/${schoolId}`);
        const schoolNameSnapshot = await get(schoolNameRef);
        if (schoolNameSnapshot.exists()) {
            return schoolNameSnapshot.val();
        } else {
            console.warn(`School name not found for school ID: ${schoolId}`);
            return null;
        }
    } catch (error) {
        console.error(`Error fetching school name for school ID: ${schoolId}`, error);
        return null;
    }
}

export async function fetchAndCacheAllData(schoolId) {
    try {
        const schoolName = await fetchSchoolName(schoolId);
        if (!schoolName) return;

        const paths = [
            `School_ids/${schoolId}`,
            `Users/Parents/${schoolId}/`,
            `Users/Teachers/${schoolId}/`,
            `Schools/${schoolName}/`,
            `Users/Schools/${schoolName}/`,
            `School_ids/Count`,
            `Users/Parents/Count`,
            `Users/Teachers/Count`,
            'Exits/',
            'User_ids_pno/'
        ];

        const allData = {};

        for (const path of paths) {
            const dataRef = ref(database, path);
            try {
                const snapshot = await get(dataRef);
                if (snapshot.exists()) {
                    allData[path] = snapshot.val();
                }
            } catch (error) {
                console.error(`Error fetching data from path: ${path}`, error);
            }
        }

        // Filter Exits data based on schoolId
        if (allData['Exits/']) {
            allData['Exits/'] = Object.fromEntries(
                Object.entries(allData['Exits/']).filter(([key, value]) => value === String(schoolId))
            );
        }

        // Filter User_ids_pno based on filtered Exits phone numbers
        if (allData['User_ids_pno/'] && allData['Exits/']) {
            const validPhoneNumbers = Object.keys(allData['Exits/']);
            allData['User_ids_pno/'] = Object.fromEntries(
                Object.entries(allData['User_ids_pno/']).filter(([key]) => validPhoneNumbers.includes(key))
            );
        }

        // Cache the fetched data locally using IndexedDB
        await storeData('schoolData', schoolId, allData);
        console.log("Filtered school data cached locally:", allData);
    } catch (error) {
        console.error("Error fetching and caching all data:", error);
    }
}

export async function getCachedData(schoolId) {
    try {
        const cachedData = await getData('schoolData', schoolId);
        return cachedData ? cachedData : null;
    } catch (error) {
        console.error("Error getting cached data:", error);
        return null;
    }
}

export async function listenForUpdates(schoolId) {
    try {
        const schoolName = await fetchSchoolName(schoolId);
        if (!schoolName) return;

        const paths = [
            `School_ids/${schoolId}`,
            `Users/Parents/${schoolId}/`,
            `Users/Teachers/${schoolId}/`,
            `Schools/${schoolName}/`,
            `Users/Schools/${schoolName}/`,
            `School_ids/Count`,
            `Users/Parents/Count`,
            `Users/Teachers/Count`,
            'Exits/',
            'User_ids_pno/'
        ];

        paths.forEach(path => {
            const dataRef = ref(database, path);
            onValue(dataRef, async (snapshot) => {
                if (snapshot.exists()) {
                    const data = snapshot.val();
                    const cachedData = await getCachedData(schoolId) || {};
                    cachedData[path] = data;

                    // Filter the data for the Exits and User_ids_pno paths
                    if (path === 'Exits/') {
                        cachedData[path] = Object.fromEntries(
                            Object.entries(data).filter(([key, value]) => value === String(schoolId))
                        );
                    } else if (path === 'User_ids_pno/' && cachedData['Exits/']) {
                        const validPhoneNumbers = Object.keys(cachedData['Exits/']);
                        cachedData[path] = Object.fromEntries(
                            Object.entries(data).filter(([key]) => validPhoneNumbers.includes(key))
                        );
                    }

                    await storeData('schoolData', schoolId, cachedData);
                }
            });
        });
    } catch (error) {
        console.error("Error listening for updates:", error);
    }
}

// Data Synchronization Function
export async function syncDataWithFirebase(schoolId) {
    try {
        const cachedData = await getCachedData(schoolId);

        if (cachedData) {
            // Extract data for all paths that need synchronization
            const paths = [
                `School_ids/${schoolId}`,
                `Users/Parents/${schoolId}/`,
                `Users/Teachers/${schoolId}/`,
                `Schools/${cachedData['School_ids']}`,
                `Users/Schools/${cachedData['School_ids']}`,
                'Exits/',
                'User_ids_pno/'
            ];

            // Iterate through paths to synchronize each one
            for (const path of paths) {
                const dataRef = ref(database, path);
                if (cachedData[path]) {
                    // Update Firebase with data from IndexedDB
                    await set(dataRef, cachedData[path]);
                    console.log(`Data synced to Firebase at path: ${path}`);
                }
            }
        } else {
            console.log("No cached data to sync.");
        }
    } catch (error) {
        console.error("Error syncing data with Firebase:", error);
    }
}

// Function to delete data from both Firebase and IndexedDB
export async function deleteData(schoolId, path) {
    try {
        // Delete data from Firebase
        await deleteFromFirebase(path);
        
        // Delete data from IndexedDB
        await deleteFromIndexedDB(schoolId);
        
        console.log(`Data deleted successfully from Firebase and IndexedDB for path: ${path}`);
    } catch (error) {
        console.error(`Error deleting data: ${error}`);
    }
}

async function deleteFromFirebase(path) {
    try {
        const dbRef = ref(database, path);
        await remove(dbRef);
        console.log(`Data removed from Firebase at path: ${path}`);
    } catch (error) {
        console.error(`Error removing data from Firebase at path: ${path}`, error);
    }
}

async function deleteFromIndexedDB(schoolId) {
    try {
        const db = await openDb();
        return new Promise((resolve, reject) => {
            const transaction = db.transaction('schoolData', 'readwrite');
            const store = transaction.objectStore('schoolData');
            const request = store.delete(schoolId);
            request.onsuccess = () => resolve();
            request.onerror = (event) => reject(`Error deleting data from IndexedDB: ${event.target.errorCode}`);
        });
    } catch (error) {
        console.error(`Error deleting data from IndexedDB for schoolId: ${schoolId}`, error);
    }
}

        
   

