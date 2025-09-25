<script>
document.addEventListener('DOMContentLoaded', async () => {
    if (typeof window.ethereum !== 'undefined') {
        console.log('MetaMask is available');
        try {
            const accounts = await ethereum.request({ method: 'eth_requestAccounts' });
            console.log('Connected account:', accounts[0]);
            window.userAccount = accounts[0];
        } catch (err) {
            console.error('User rejected connection:', err);
        }
    } else {
        alert('MetaMask is not installed. Please install it to use this feature.');
    }
});

const contractABI = [
  {"anonymous":false,"inputs":[{"indexed":false,"internalType":"string","name":"caseId","type":"string"},{"indexed":false,"internalType":"string","name":"assignmentHash","type":"string"},{"indexed":false,"internalType":"uint256","name":"timestamp","type":"uint256"}],"name":"AssignmentHashLogged","type":"event"},
  {"anonymous":false,"inputs":[{"indexed":false,"internalType":"string","name":"caseId","type":"string"},{"indexed":false,"internalType":"string","name":"closureHash","type":"string"},{"indexed":false,"internalType":"uint256","name":"timestamp","type":"uint256"}],"name":"CaseClosureLogged","type":"event"},
  {"anonymous":false,"inputs":[{"indexed":false,"internalType":"string","name":"caseId","type":"string"},{"indexed":false,"internalType":"string","name":"txHash","type":"string"},{"indexed":false,"internalType":"uint256","name":"timestamp","type":"uint256"}],"name":"CaseTransactionLogged","type":"event"},
  {"anonymous":false,"inputs":[{"indexed":false,"internalType":"string","name":"caseId","type":"string"},{"indexed":false,"internalType":"string","name":"evidenceHash","type":"string"},{"indexed":false,"internalType":"uint256","name":"timestamp","type":"uint256"}],"name":"EvidenceHashLogged","type":"event"},
  {"inputs":[{"internalType":"string","name":"_caseId","type":"string"},{"internalType":"string","name":"_txHash","type":"string"}],"name":"logCaseTransaction","outputs":[],"stateMutability":"nonpayable","type":"function"},
  {"inputs":[{"internalType":"string","name":"_caseId","type":"string"},{"internalType":"string","name":"_evidenceHash","type":"string"}],"name":"logEvidenceHash","outputs":[],"stateMutability":"nonpayable","type":"function"},
  {"inputs":[{"internalType":"string","name":"_caseId","type":"string"},{"internalType":"string","name":"_assignmentHash","type":"string"}],"name":"logAssignmentHash","outputs":[],"stateMutability":"nonpayable","type":"function"},
  {"inputs":[{"internalType":"string","name":"_caseId","type":"string"},{"internalType":"string","name":"_closureHash","type":"string"}],"name":"logCaseClosure","outputs":[],"stateMutability":"nonpayable","type":"function"},
  {"inputs":[{"internalType":"string","name":"_caseId","type":"string"}],"name":"getCaseRecord","outputs":[{"internalType":"string","name":"txHash","type":"string"},{"internalType":"string","name":"evidenceHash","type":"string"},{"internalType":"string","name":"assignmentHash","type":"string"},{"internalType":"string","name":"closureHash","type":"string"},{"internalType":"uint256","name":"createdAt","type":"uint256"},{"internalType":"uint256","name":"updatedAt","type":"uint256"},{"internalType":"bool","name":"isClosed","type":"bool"}],"stateMutability":"view","type":"function"}
];

const contractAddress = "0xfc337a351ac275c6e830deee8b114f8bb1878b9e";
let contract;

async function initContract() {
    if (typeof window.ethereum !== 'undefined') {
        const provider = new ethers.providers.Web3Provider(window.ethereum);
        const signer = provider.getSigner();
        contract = new ethers.Contract(contractAddress, contractABI, signer);
        console.log('Smart contract loaded');
    }
}

// Returns receipt and rethrows on error
async function logCaseTransaction(caseId, txHash) {
    if (!contract) await initContract();
    try {
        const tx = await contract.logCaseTransaction(caseId, txHash);
        console.log('Transaction sent:', tx.hash);
        const receipt = await tx.wait();
        console.log('Transaction confirmed:', receipt);
        alert('Case transaction hash logged on blockchain!');
        return receipt;
    } catch (err) {
        console.error('Failed to log case transaction:', err);
        throw err;
    }
}

// Returns receipt and rethrows on error
async function logEvidence(caseId, evidenceHash) {
    if (!contract) await initContract();
    try {
        const tx = await contract.logEvidenceHash(caseId, evidenceHash);
        console.log('Transaction sent:', tx.hash);
        const receipt = await tx.wait();
        console.log('Transaction confirmed:', receipt);
        alert('Evidence hash logged on blockchain!');
        return receipt;
    } catch (err) {
        console.error('Failed to log evidence:', err);
        throw err;
    }
}

// Returns receipt and rethrows on error
async function logAssignment(caseId, assignmentHash) {
    if (!contract) await initContract();
    try {
        const tx = await contract.logAssignmentHash(caseId, assignmentHash);
        console.log('Transaction sent:', tx.hash);
        const receipt = await tx.wait();
        console.log('Transaction confirmed:', receipt);
        alert('Assignment hash logged on blockchain!');
        return receipt;
    } catch (err) {
        console.error('Failed to log assignment:', err);
        throw err;
    }
}

// Returns receipt and rethrows on error
async function logCaseClosure(caseId, closureHash) {
    if (!contract) await initContract();
    try {
        const tx = await contract.logCaseClosure(caseId, closureHash);
        console.log('Transaction sent:', tx.hash);
        const receipt = await tx.wait();
        console.log('Transaction confirmed:', receipt);
        alert('Case closure hash logged on blockchain!');
        return receipt;
    } catch (err) {
        console.error('Failed to log case closure:', err);
        throw err;
    }
}

//  (read-only function)
async function getCaseRecord(caseId) {
    if (!contract) await initContract();
    try {
        const record = await contract.getCaseRecord(caseId);
        console.log('Fetched case record:', record);
        alert(`Case Record:\nTxHash: ${record.txHash}\nEvidence: ${record.evidenceHash}\nAssignment: ${record.assignmentHash}\nClosure: ${record.closureHash}\nCreated: ${new Date(record.createdAt * 1000).toLocaleString()}\nUpdated: ${new Date(record.updatedAt * 1000).toLocaleString()}\nClosed: ${record.isClosed}`);
        return record;
    } catch (err) {
        console.error('Failed to fetch case record:', err);
        throw err;
    }
}
</script>
