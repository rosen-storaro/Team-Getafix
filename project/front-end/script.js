function saveTOken(token){
  localStorage.setItem('authToken', token);
}
function getToken(){
  return localStorage.getItem('authToken');
}
async function login(username, password) {
  try {
    const response = await fetch('https://api.school-system.com/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ username, password })
    });

    const data = await response.json();

    if (response.ok && data.token) {
      saveToken(data.token);
      console.log('✅ Logged in successfully');
    } else {
      console.error('❌ Login failed:', data.message || 'No token received');
    }
  } catch (error) {
    console.error('❌ Login error:', error);
  }
}
async function fetchInventory(){
  try{
    const response = await fetch('https://api.school-system.com/inventory', {
      headers:{
        'Authorization': 'Bearer ' + getToken()
      }
  });
  const data = await response.json();
  console.log('✅ Inventory fetched successfully:', data);
  return data;
}catch(error){
  console.error('❌ Inventory fetch error:', error);
}
}

async function borrowItem(intemId){
  try{
    const response = await fetch('https://api.school-system.com/inventory/${itemId}/borrow', {
      method: 'POST',
      headers:{
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + getToken()
      },
      body: JSON.stringify({itemId})
    });
    const data = await response.json();
    console.log('✅ Item borrowed successfully:', data);
  }catch(error){
    console.error('❌ Borrow item error:', error);
  }

  }

  async function returnItem(itemId) 
  {
    try{
      const response = await fetch('https://api.school-system.com/inventory/${itemId}/return', {
        method: 'POST',
        headers:{
          'Authorization': 'Bearer ' + getToken()  
      }
    });
    const data = await response.json();
    console.log('✅ Item returned successfully:', data);
  }catch(error){
    console.error('❌ Return item error:', error);
  } 
  }
  async function exportReport() {
    try{
      const response = await fetch('https://api.school-system.com/reports/export', {
        headers: {
          'Authorization': 'Bearer ' + getToken()
        }
    });

    

    const blod = await response.blod();
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = '//something';
    document.body.appendChild(a);
    a.click();
    a.remove();

    console.log('✅ Report exported successfully');
  }catch(error){
    console.error('❌ Report export error:', error);
  }
}
