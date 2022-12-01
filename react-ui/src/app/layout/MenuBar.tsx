import { observer } from "mobx-react-lite";
import { Link, NavLink } from "react-router-dom";
import { Container, Dropdown, Icon, Menu } from "semantic-ui-react";
import { useStore } from "../stores/store";

export default observer( function MenuBar() {
    const { userStore: {user, logout, isLoggedIn} } = useStore()

    return(
        <Menu fixed="top">
            <Container>
                <Menu.Item header>
                    <Icon name="paper plane outline" size="big"/>
                    Kontolene
                </Menu.Item>
                <Menu.Item as={NavLink} to='/'>
                    Threads
                </Menu.Item>
                <Menu.Item position='right'>
                    <Dropdown pointing='top left' text={user?.username ?? ""}>
                        <Dropdown.Menu>
                            {isLoggedIn ?
                                <Dropdown.Item onClick={logout} text='Logout' icon='power' /> :
                                <>
                                    <Dropdown.Item as={Link} to="/login" text='Login to account' icon='user' />
                                    <Dropdown.Item as={Link} to="/createUser" text='Create New User' icon='save' />
                                </>
                            }
                        </Dropdown.Menu>
                    </Dropdown>
                </Menu.Item>
            </Container>
        </Menu>
    )
})