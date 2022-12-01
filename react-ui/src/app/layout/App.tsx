import { useEffect } from 'react';
import { Container } from 'semantic-ui-react';
import { Route, Routes } from 'react-router-dom';
import { useStore } from '../stores/store';
import { observer } from 'mobx-react-lite';
import MenuBar from './MenuBar';
import ThreadsView from '../features/threads/ThreadsView';
import ThreadsPage from '../features/threads/ThreadPage';
import LoginForm from '../features/login/LoginForm';
import CreateUser from '../features/login/CreateUser';


function App() {
  const {threadStore, userStore} = useStore();
  
  useEffect(() => {
    threadStore.loadThreads();

    if(userStore.token){
      userStore.getUser();
    }

  }, [threadStore, userStore])
  return (
    <>
      <MenuBar />
      <Container style={{marginTop: '112px'}}>
        <Routes>
          <Route path='/' element={<ThreadsView threads={threadStore.threads}/>}/>
          <Route path='/thread/:id' element={<ThreadsPage />}/>
          <Route path='/login' element={<LoginForm />}/>
          <Route path='/createUser' element={<CreateUser />}></Route>
        </Routes>
      </Container>
    </>
  );
}

export default observer(App);
